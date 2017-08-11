<?php

namespace daifuku\parser;

interface Parser {
	
	public function getName();

	public function parseHTML($buf);
}