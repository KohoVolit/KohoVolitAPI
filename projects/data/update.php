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

// Slovak parliament
$report .= update(array('parliament' => 'sk/nrsr', 'conflict_mps' => ''));

// Slovak local assemblies
$sk_starostovia_conflicts =
	'529460-Vladimír-Bajan->sk/nrsr/9,
	528595-Tatiana-Rosová->sk/nrsr/693,
	501069-Peter-Tóth->,
	527165-Jaroslav-Verba->,
	508497-Jaroslav-Demian->sk/nrsr/749,
	524239-Jozef-Hodoši->,
	580449-Anton-Zima->,
	501077-János-Szigeti->sk/nrsr/338,
	504297-Vladimír-Jánoš->,
	505919-Dušan-Bublavý->sk/nrsr/778,
	525596-Iveta-Potočná->,
	527254-Miloš-Vaňušaník->,
	518361-Anna-Janovicová->,
	523445-Michal-Ryša->,
	520187-Mikuláš-Juščík->sk/nrsr/270,
	518123-Irena-Gajdošová->,
	521400-Miloš-Barcal->,
	544213-Štefan-Straka->,
	508624-Vladimír-Bušniak->,
	581747-Simona-Holicová->,
	507059-Ján-Hrčka->,
	520004-Jana-Vaľová->sk/nrsr/732,
	503819-Ľubomír-Petrák->sk/nrsr/683,
	504394-Anton-Ivánek->,
	507946-Ján-Podmanický->sk/nrsr/685,
	510505-Jozef-Dufala->,
	524573-Marián-Dujava->,
	510513-Juraj-Poproč->,
	528773-Dušan-Baranský->,
	517682-Janka-Stupňanová->,
	599981-Richard-Raši->sk/nrsr/761,
	598224-Rudolf-Bauer->sk/nrsr/13,
	525855-Adrián-Dáni->,
	528455-Mária-Kaňuchová->,
	502448-Tibor-Tóth->,
	528811-Marcela-Baškovská->,
	528471-Ján-Geňo->,
	524701-Pavol-Veteráni->,
	515167-Oľga-Rízová->,
	510262-Alexander-Slafkovský->sk/nrsr/204,
	522732-Ján-Šimko->,
	517046-Eduard-Baláž->,
	522741-Eduard-Baláž->,
	519545-Alena-Voľanská-Martičeková->,
	505064-Ľubomír-Goga->,
	500607-Jozef-Kováč->,
	509876-Jaroslav-Rosina->,
	521809-Milan-Hudák->,
	509906-Viera-Mazúrová->sk/nrsr/734,
	501310-Olga-Szabó->sk/nrsr/126,
	521884-František-Petro->,
	517143-Ľubica-Kordíková->,
	505340-Miroslav-Jánošík->,
	512508-Miroslav-Jánošík->,
	508870-Jozef-Kalman->,
	515345-Ján-Pervan->,
	514349-Stanislav-Bartoš->sk/nrsr/12,
	521906-Igor-Antoni->,
	527769-Iveta-Horvatová->,
	557765-Oskár-Tóth->,
	515426-Jaroslav-Suja->,
	514462-Jozef-Šimko->,
	529061-Jana-Paľová->,
	520721-Stanislav-Viravec->,
	527777-Stanislav-Viravec->,
	521949-Vladimír-Kišdučák->,
	515507-Július-Brašo->,
	518727-Ľubica-Jergušová->,
	523054-Peter-Saboslai->,
	510050-Jozef-Gabriel->,
	509477-Ján-Podmanický->,
	517976-Ján-Poliak->,
	527840-Peter-Obrimčák->sk/nrsr/718,
	522066-Jozef-Lukáč->,
	525260-Stanislav-Bartoš->,
	511897-Zoltán-Végh->,
	504076-Zoltán-Horváth->,
	515612-Anna-Szögedi->sk/nrsr/700,
	512672-Ján-Lukáč->,
	525332-Ján-Varga->,
	518051-Miroslav-Chovanec->,
	510114-Ivan-Šaško->sk/nrsr/701,
	527971-Milan-Hudák->,
	525383-Anton-Štefko->,
	512001-Jozef-Líška->,
	543870-Elemér-Jakab->sk/nrsr/710,
	558214-Ján-Jakab->,
	543705-Milan-Grega->,
	516511-Anna-Makovníková->,
	500933-Tibor-Tóth->sk/nrsr/306,
	525421-František-Novák->,
	524115-Jozef-Pisarčík->,
	513814-Jozef-Kollár->,
	523348-Vladimír-Kišák->,
	519961-Ján-Lukáč->,
	521108-Jozef-Gajdoš->sk/nrsr/42,
	517402-Igor-Choma->sk/nrsr/790';
/*
// areas are updated only when running the update for the first time
$report .= update(array('parliament' => 'sk/starostovia', 'area' => true, 'conflict_mps' => $sk_starostovia_conflicts));
*/
$report .= update(array('parliament' => 'sk/starostovia', 'conflict_mps' => $sk_starostovia_conflicts));

// manual corrections of the data for all parliaments
$report .= update(array('parliament' => 'corrections'));

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
