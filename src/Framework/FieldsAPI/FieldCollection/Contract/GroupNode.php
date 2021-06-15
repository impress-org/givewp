<?php

namespace Give\Framework\FieldsAPI\FieldCollection\Contract;

interface GroupNode extends Node {
	public function getFields();
	public function append( Node $node );
	public function getNodeIndexByName( $name );
	public function getNodeByName( $name );
	public function count();
}
