<?php
declare(strict_types=1);

namespace ItalyStrap\Cache;

trait KeyValidatorTrait {

	use \Fig\Cache\KeyValidatorTrait {
		validateKey as validateKeyTrait;
	}

	/**
	 * @param mixed $key
	 * @return bool
	 */
	private function validateKey($key): bool {
		if ($key === 0) {
			$key = '0';
		}

		/**
		 * @psalm-suppress MixedArgument
		 */
		return $this->validateKeyTrait($key);
	}
}
