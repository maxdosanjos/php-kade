<?
	class Util{
		public static function bigIntval($value) 
		{
			  $value = trim((string)$value);
			  if (ctype_digit($value)) {
				return $value;
			  }
			  $value = preg_replace("/[^0-9](.*)$/", '', $value);
			  if (ctype_digit($value)) {
				return $value;
			  }
			  return 0;
		}

	}
?>