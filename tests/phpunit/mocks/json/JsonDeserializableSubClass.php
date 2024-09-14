<?php

namespace MediaWiki\Tests\Json;

use MediaWiki\Json\JsonDeserializableTrait;
use MediaWiki\Json\JsonDeserializer;

/**
 * Testing class for JsonDeserializer unit tests.
 */
class JsonDeserializableSubClass extends JsonDeserializableSuperClass {
	use JsonDeserializableTrait;

	/** @var mixed */
	private $subClassField;

	public function __construct( $superClassFieldValue, $subClassFieldValue ) {
		parent::__construct( $superClassFieldValue );
		$this->subClassField = $subClassFieldValue;
	}

	public function getSubClassField() {
		return $this->subClassField;
	}

	public static function newFromJsonArray( JsonDeserializer $deserializer, array $json ) {
		return new self( $json['super_class_field'], $json['sub_class_field'] );
	}

	protected function toJsonArray(): array {
		return parent::toJsonArray() + [
			'sub_class_field' => $this->getSubClassField()
		];
	}
}
