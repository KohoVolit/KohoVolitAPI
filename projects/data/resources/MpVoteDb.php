<?php

/**
 * \ingroup data
 *
 * Inserts sqlite database (downloaded from scraperwiki) into database table mp_vote
 *
 * \internal
 * This class is an entry point for inserting the sqlite db for individual parliaments.
 * \endinternal
 */
class MpVoteDb
{
	/**
	 * Inserts sqlite database (downloaded from scraperwiki) into database.
	 *
	 * \internal
	 * It actually includes a MpVoteTransfer<parliament code> class specific for the given parliament and returns the result of its update() method.
	 * \endinternal	 
	 *
	 * \param $params An array of pairs <em>param => value</em> specifying the parliament and its remote resource to transfer.
	 * Parameter \c parliament specifies the parliament. Other parameters are specific to a transfer for the particular parliament.
	 * Common parameter is 'check'; if set, it checks for possible problems during update
	 *
	 * \return Result of the update process.
	 *
	 * \ex
	 * \code
	 * update(array('parliament' => 'cz/psp'))
	 * \endcode returns
	 * \code
	 * Array
	 * (
	 *     [update] => OK
	 * )
	 * \endcode
	 */
	public function update($params)
	{
		$parliament = $params['parliament'];
		$transfer = 'MpVoteDb' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = include_once API_ROOT . "/projects/data/resources/$parliament/$transfer.php";
		if (!$ok)
			throw new Exception("The API resource <em>MpVoteDb</em> is not implemented for parliament <em>$parliament</em>.", 400);

		$transfer_class = new $transfer($params);
		return $transfer_class->update($params);
	}
	
	public function read($params)
	{
		$parliament = $params['parliament'];
		$transfer = 'MpVoteDb' . str_replace(' ', '', ucwords(strtr($parliament, '/-', '  ')));
		$ok = include_once API_ROOT . "/projects/data/resources/$parliament/$transfer.php";
		//correct empty parliament
		if ($parliament == '') $ok = false;
		if (!$ok)
			throw new Exception("The API resource <em>MpVoteDb</em> is not implemented for parliament: <em>$parliament</em>.", 400);

		$params['check'] = true;
		$transfer_class = new $transfer($params);
		return $transfer_class->check($params);
	}
}

?>
