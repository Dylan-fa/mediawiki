<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 * @file
 */

declare( strict_types=1 );

namespace Wikimedia\Metrics\Metrics;

use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Wikimedia\Metrics\Exceptions\IllegalOperationException;
use Wikimedia\Metrics\Sample;

/**
 * Gauge Metric Implementation
 *
 * Gauge Metrics can be set to any numeric value and are identified by type "g".
 *
 * @author Cole White
 * @since 1.38
 */
class GaugeMetric implements MetricInterface {

	/**
	 * The StatsD protocol type indicator:
	 * https://github.com/statsd/statsd/blob/v0.9.0/docs/metric_types.md
	 * https://docs.datadoghq.com/developers/dogstatsd/datagram_shell/?tab=metrics
	 *
	 * @var string
	 */
	private const TYPE_INDICATOR = "g";

	/** @var BaseMetricInterface */
	private BaseMetricInterface $baseMetric;

	/** @var LoggerInterface */
	private LoggerInterface $logger;

	/** @inheritDoc */
	public function __construct( $baseMetric, $logger ) {
		$this->baseMetric = $baseMetric;
		$this->logger = $logger;
	}

	/**
	 * Sets metric to value.
	 *
	 * @param float $value
	 * @param string[] $labels
	 * @return void
	 */
	public function set( float $value, array $labels = [] ): void {
		$this->baseMetric->clearLabels();
		foreach ( $this->baseMetric->getLabelKeys() as $i => $labelKey ) {
			$this->baseMetric->addLabel( $labelKey, $labels[$i] );
		}
		$this->baseMetric->addSample( new Sample( $this->baseMetric->getLabelValues(), $value ) );
	}

	/** @inheritDoc */
	public function getName(): string {
		return $this->baseMetric->getName();
	}

	/** @inheritDoc */
	public function getComponent(): string {
		return $this->baseMetric->getComponent();
	}

	/** @inheritDoc */
	public function getTypeIndicator(): string {
		return self::TYPE_INDICATOR;
	}

	/** @inheritDoc */
	public function getSamples(): array {
		return $this->baseMetric->getSamples();
	}

	/** @inheritDoc */
	public function getSampleRate(): float {
		return $this->baseMetric->getSampleRate();
	}

	/** @inheritDoc */
	public function withSampleRate( float $sampleRate ) {
		try {
			$this->baseMetric->setSampleRate( $sampleRate );
		} catch ( IllegalOperationException | InvalidArgumentException $ex ) {
			$this->logger->error( $ex->getMessage() );
			return new NullMetric;
		}
		return $this;
	}

	/** @inheritDoc */
	public function getLabelKeys(): array {
		return $this->baseMetric->getLabelKeys();
	}

	/** @inheritDoc */
	public function withLabelKey( string $key ): GaugeMetric {
		$this->baseMetric->addLabelKey( $key );
		return $this;
	}

	/** @inheritDoc */
	public function withLabel( string $key, string $value ) {
		try {
			$this->baseMetric->addLabel( $key, $value );
			$this->baseMetric->clearLabels(); // Support legacy behavior for now
		} catch ( IllegalOperationException | InvalidArgumentException $ex ) {
			$this->logger->error( $ex->getMessage() );
			return new NullMetric;
		}
		return $this;
	}

	/** @inheritDoc */
	public function fresh(): GaugeMetric {
		$this->baseMetric->clearLabels();
		return $this;
	}
}
