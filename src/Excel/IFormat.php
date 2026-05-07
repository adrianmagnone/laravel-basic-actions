<?php

namespace Aiglos\Lba\Excel;

interface IFormat
{
    public static function headerStyle() : Array;

	public static function h1Style() : Array;

	public static function h2Style() : Array;

	public static function bodyStyle() : Array;

	public static function footerStyle() : Array;

	public static function headerGroupStyle() : Array;

	public static function cellFormats(String $tipo) : Array;

	public static function tableFormats() : Array;
}