<?php

##
# © 2015 Partners HealthCare System, Inc. All Rights Reserved. 
##

/**
 * PLUGIN NAME: Consent Form tracking - eReg binder
 * DESCRIPTION:
 * VERSION: 1.0
 * AUTHOR: Dimitar Dimitrov
 */

// Call the REDCap Connect file in the main "redcap" directory
require_once "../../redcap_connect.php";

// Display the project header
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';
require_once "common_functions.php";
print '<script type="text/javascript" src="jquery.tablesorter.min.js"></script>';
print "<script type=\"text/javascript\">
    $(document).ready(function() 
    { 
        $(\"#record_status_table\").tablesorter({sortList:[[3,0],[5,0]]}); 
    } 
); 
</script>";

if(!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {    
    exit('Project ID is missing! Cannot continue!');
}

// Record ID	
// Review Type 	
// Review #	
// Form Type 	
// Description of Subject Population	
// IRB Valid Date 	
// IRB Expiration Date	
// Option Section	
// Consent Form  Changes

/**$subtitle = RCView::h1(array('class'=>'title', 'style'=>'text-align:center; color:#800000; font-size:16px;font-weight:bold;padding:5px;'), 
	'CONSENT FORM VERSION TRACKING LOG');
$subtitle .= RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
	'Protocol Title: protocoltitle');
$subtitle .= RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
	'Protocol Number: [protocolnumber]');
//echo var_export($Proj->metadata['protocoltitle'],true);
echo $subtitle;*/

$metadata = $Proj->metadata;

$headers = RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Record ID");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),
	isset($metadata['doctype']['element_label']) ? $metadata['doctype']['element_label'] : "Review Type");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),
	isset($metadata['docnumber']['element_label']) ? $metadata['docnumber']['element_label'] : "Review #");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Form Type");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Description of Subject Population");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"IRB Valid Date");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"IRB Expiration Date");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Option Section");
$headers .= RCView::th(array('class'=>'', 'style'=>'text-align:center;color:#800000;padding:5px 10px;vertical-align:bottom;'),"Consent Form Changes");
$rpt_table_rows = RCView::thead('', RCView::tr('', $headers));

$all_records = Records::getData($Proj->project_id, 'array', null, $Proj->table_pk);
$numstaff = explode('\\n', $Proj->metadata['numstaff']['element_enum']);

$all_forms = array_keys($Proj->forms);
$first_form_name = $all_forms[0];
$record_entry           = APP_PATH_WEBROOT . "DataEntry/index.php?pid=".$_GET['pid']."&page=".urlencode($first_form_name);
$no_data_for_records = array();
$rows_unsorted = array();
foreach ( array_keys($all_records) as $record_id ) {
    $record_data = Records::getData('array', $record_id);
    $iDocs = 0;    
    if(isset ($record_data) && isset($record_data[$record_id]) ) {
	$keys = array_keys($record_data[$record_id]);
	$this_record_data = array_shift($record_data[$record_id]);
	$iDocs = $this_record_data['totalconsentformversions'];
	if(strlen(trim($iDocs))<=0) {
	    $iDocs = 0;
	    $no_data_for_records[] = $record_id;
	}
	if(strlen($protocolnumber)<=1) $protocolnumber = $this_record_data['protocolnumber'];
	if(strlen($protocoltitle)<=1) $protocoltitle = $this_record_data['protocoltitle'];
    }
    for ( $i=1; $i<=$iDocs; $i++ ) {
	$record_row = '';
	$record_row = RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),
	    RCView::a(array('href'=>$record_entry.'&id='.$record_id, 'style'=>'color:#800000;vertical-align:middle;text-decoration:underline;font-weight:bold;'), $record_id));
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$this_record_data['doctype']);
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$this_record_data['docnumber']);
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),parse_element_enum ($this_record_data['typeconsent'.$i],$metadata['typeconsent'.$i]['element_enum']) );
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$this_record_data['populationconsent'.$i]);
	$ivd = ' - ';
	if(isset($this_record_data['validdateconsent'.$i]) && strlen($this_record_data['validdateconsent'.$i])>0) {
		$ivd = date ( 'm/d/Y', strtotime($this_record_data['validdateconsent'.$i]));
	}
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$ivd);
	$ied = ' - ';
	if(isset($this_record_data['expiredateconsent'.$i]) && strlen($this_record_data['expiredateconsent'.$i])>0) {
		$ied = date ( 'm/d/Y', strtotime($this_record_data['expiredateconsent'.$i]));
	}
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$ied);
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),parse_element_enum ($this_record_data['optionconsent'.$i],$metadata['optionconsent'.$i]['element_enum']));
	$record_row .= RCView::td(array('class'=>'data', 'style'=>'text-align:center;padding:5px 10px;vertical-align:center;'),$this_record_data['changesconsent'.$i]);
	
	$rpt_table_rows .= RCView::tr('',$record_row);
    }
}
$subtitle = RCView::h1(array('class'=>'title', 'style'=>'text-align:center; color:#800000; font-size:16px;font-weight:bold;padding:5px;'), 
	'CONSENT FORM VERSION TRACKING LOG');
$subtitle .= RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
	'Protocol Title: '.$protocoltitle);
$subtitle .= RCView::h2(array('class'=>'subtitle', 'style'=>'text-align:left; font-size:15px;color:#800000'), 
	'Protocol Number: '.$protocolnumber);
$subtitle .= RCView::img(array('src'=>APP_PATH_IMAGES.'printer.png','class'=>'imgfix')) . 
		"<a href='javascript:;' style='font-size:11px;' onclick=\"window.print();\">{$lang['graphical_view_15']}</a>";	

print $subtitle;
print '<p/>';
print 'Note: You can sort the data by clicking on the data headers';
$prt_table_complete = RCView::table(array('id'=>'record_status_table', 'class'=>'tablesorter'), $rpt_table_rows);
print $prt_table_complete;
