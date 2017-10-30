<?php

use havana\dbobject;

class Person extends dbobject
{
	const TABLE_NAME = 'people';

	public $name;
}
