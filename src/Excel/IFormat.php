<?php

namespace Aiglos\Lba\Excel;

interface IFormat
{
    public function headerStyle() : Array;

	public function h1Style() : Array;

	public function h2Style() : Array;

	public function bodyStyle() : Array;

	public function footerStyle() : Array;

	public function headerGroupStyle() : Array;

	public function cellFormats(String $tipo) : Array;
}