<?php
class IQFM_BaseView {

	public function setbackground_close_x() {
		$javascript = '
			<script type="text/javascript" >
				$(document).ready(function(){jQuery(".close_x").css("background",  "url(\''.WPIQF_PLUGIN_URL.'/css/images/close.JPG\')");});
			</script>';
		return $javascript;
	}

	public function set_default_tab() {
		if (array_key_exists('iqfm-formid', $_GET)) {
			$javascript = '
				<script type="text/javascript" >
					$(document).ready(function(){jQuery("#tabtitle'.$_GET['iqfm-formid'].'").click()});
				</script>';
			return $javascript;
		} else {
			return '';
		}
	}
}
?>