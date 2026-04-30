<?php
class inquiry_form_element_loader {
	static public function get_elementname(){
		return array(
					'textbox',
					'textarea',
					'selectbox',
					'checkbox',
					'radio',
					'email',
					'tel',
					'zip',
					'file'
					);
	}
	
	static public function require_once_element($dir){
		foreach (self::get_elementname() as $elementname) {
			require_once ($dir.$elementname.'.php');
		}
	}
}
