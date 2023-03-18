<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

trait KeyValidatorTrait {

	use \Fig\Cache\KeyValidatorTrait {
		validateKey as validateKeyTrait;
	}

	private function validateKey($key): bool {
		if ($key === 0) {
			$key = (string)$key;
		}

		return $this->validateKeyTrait($key);
	}
}
