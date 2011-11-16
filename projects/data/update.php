<?php

require '/home/shared/api.kohovolit.eu/config/settings.php';
require '/home/shared/api.kohovolit.eu/setup.php';

$report = '';

// Czech parliament - Chamber of Deputies
/*
// previous terms of office are updated only when running the update for the first time
$report .= update(array('parliament' => 'cz/psp', 'term' => '1', 'conflict_mps' => '117->, 118->'));
$report .= update(array('parliament' => 'cz/psp', 'term' => '2', 'conflict_mps' => '329->cz/psp/189'));
$report .= update(array('parliament' => 'cz/psp', 'term' => '3', 'conflict_mps' => '348->, 375->, 387->, 388->cz/psp/223'));
$report .= update(array('parliament' => 'cz/psp', 'term' => '4', 'conflict_mps' => '5253->cz/psp/387, 5254->'));
$report .= update(array('parliament' => 'cz/psp', 'term' => '5', 'conflict_mps' => '5455->, 5775->, 5505->'));
$report .= update(array('parliament' => 'cz/psp', 'term' => '6', 'conflict_mps' => '5991->, 5992->, 5993->, 5964->'));
*/
$report .= update(array('parliament' => 'cz/psp', 'conflict_mps' => '5991->, 5992->, 5993->, 5964->'));

// Czech parliament - Senate
/*
// areas are updated only when running the update for the first time
$report .= update(array('parliament' => 'cz/senat', 'area' => 'true', 'conflict_mps' => '14->, 215->cz/psp/5265, 216->cz/psp/335, 236->cz/psp/5256, 253->cz/psp/1016, 226->cz/psp/5302, 246->cz/psp/5514, 247->cz/psp/124, 66->cz/psp/5471, 248->cz/psp/5865, 252->cz/psp/239'));
*/
$report .= update(array('parliament' => 'cz/senat', 'conflict_mps' => '14->, 215->cz/psp/5265, 216->cz/psp/335, 236->cz/psp/5256, 253->cz/psp/1016, 226->cz/psp/5302, 246->cz/psp/5514, 247->cz/psp/124, 66->cz/psp/5471, 248->cz/psp/5865, 252->cz/psp/239'));

// Czech local assemblies
$report .= update(array('parliament' => 'cz/local', 'conflict_mps' =>
	'cz_nachod_2010-2014_12->cz/psp/5290,
	cz_teplice_2010-2014_15->cz/psp/379, cz_teplice_2010-2014_16->cz/senat/120, cz_teplice_2010-2014_32->cz/psp/432,
	cz_hodonin_2010-2014_13->cz/psp/109,
	cz_starostove_2010-2014_9->cz/senat/206, cz_starostove_2010-2014_19->cz/senat/239, cz_starostove_2010-2014_25->cz/cesky-krumlov/cz_cesky-krumlov_2010-2014_2, cz_starostove_2010-2014_40->cz/psp/5407, cz_starostove_2010-2014_41->cz/hodonin/cz_hodonin_2010-2014_25, cz_starostove_2010-2014_52->cz/psp/5519, cz_starostove_2010-2014_56->cz/psp/5768, cz_starostove_2010-2014_72->, cz_starostove_2010-2014_73->cz/psp/373, cz_starostove_2010-2014_91->cz/psp/5976, cz_starostove_2010-2014_104->cz/psp/257, cz_starostove_2010-2014_107->cz/nachod/cz_nachod_2010-2014_27, cz_starostove_2010-2014_127->cz/psp/5912, cz_starostove_2010-2014_134->cz/psp/5945, cz_starostove_2010-2014_139->cz/psp/5982, cz_starostove_2010-2014_160->cz/senat/210, cz_starostove_2010-2014_162->cz/psp/5265, cz_starostove_2010-2014_168->cz/senat/219, cz_starostove_2010-2014_173->cz/psp/5909, cz_starostove_2010-2014_191->cz/senat/120, cz_starostove_2010-2014_204->cz/psp/5021, cz_starostove_2010-2014_213->cz/psp/5929, cz_starostove_2010-2014_218->cz/psp/5503, cz_starostove_2010-2014_225->cz/senat/225,
	cz_zdar-nad-sazavou_2010-2014_9->cz/senat/225,
	cz_vsetin_2010-2014_1->cz/starostove/cz_starostove_2010-2014_217, cz_vsetin_2010-2014_10->cz/senat/207, cz_vsetin_2010-2014_16->cz/psp/5312,
	cz_brno_2010-2014_8->cz/psp/5303, cz_brno_2010-2014_37->, cz_brno_2010-2014_38->cz/starostove/cz_starostove_2010-2014_12'));


if (!empty($report))
	mail(API_ADMIN_EMAIL, 'Warnings or errors occured in data update', $report);

exit;


function update($params)
{
	try
	{
		$api = new ApiDirect('data');
		$res = $api->update('Updater', $params);
		$log = file_get_contents($res['log']);
		if (strpos($log, 'ERROR:') !== false || strpos($log, 'WARNING:') !== false)
		    return "Warnings or errors occured during the update of parliament {$params['parliament']}, see:\n{$res['log']}\n\n";
	}
	catch (Exception $e)
	{
		return "An exception occured during the update of parliament {$params['parliament']}: " . $e->getMessage() . "\n\n";
	}
}

?>
