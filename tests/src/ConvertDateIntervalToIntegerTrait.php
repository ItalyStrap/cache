<?php

declare(strict_types=1);

namespace ItalyStrap\Tests;

use DateInterval;
use DateTime;
use Exception;

trait ConvertDateIntervalToIntegerTrait {

	/**
	 * @param DateInterval $ttl
	 * @return int
	 * @throws Exception
	 * @author Roave\DoctrineSimpleCache;
	 */
	private function convertDateIntervalToInteger( DateInterval $ttl ) : int {
		// Timestamp has 2038 year limitation, but it's unlikely to set TTL that long.
		return (new DateTime())
			->setTimestamp(0)
			->add($ttl)
			->getTimestamp();
	}
}
