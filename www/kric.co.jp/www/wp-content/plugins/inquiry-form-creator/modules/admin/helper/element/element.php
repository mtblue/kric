<?php
abstract class inquiry_form_element{
	abstract public static function set_element_editor($form_id, $field_id);
	abstract protected static function get_element_default_value($form_id, $field_id);
}
?>