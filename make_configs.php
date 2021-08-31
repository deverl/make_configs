<?php

// Simple PHP script to read a file (first argument) containing client_id values
// (one per line, no commas) and generate migration config files from it.
//
// It defaults to 10 clients per config file, but you can override that by
// providing a second argument to the program.
//
// Usage: php make_configs.php <client_id_file> [<num_clients_per_config_file>]
//


const DEFAULT_NUM_OUTPUT_CLIENTS = 10;

if ($argc < 2) {
	echo "You must provide the input file name!" . PHP_EOL;
	exit;
}

$preamble = <<< EOL
{
    "mode": "migrate",
    "mvp_level" : 1150.0,
    "cohort": "QBDT Simple Migration",
    "migration_skip_days": 8,
    "migration_window_days": 11,
    "comments" : "QBDT Simple Migration",
    "clients" : [

EOL;

$postamble = <<< EOL
    ]
}

EOL;

$filename = $argv[1];

if ($argc > 2) {
	$num_output_clients = $argv[2];
}
else {
	$num_output_clients = DEFAULT_NUM_OUTPUT_CLIENTS;
}

$file = fopen($filename, "r");

$lines = [];

while (($line = fgets($file)) !== false) {
	$lines[] = trim($line);
}

fclose($file);

$file_counter = 0;

$total_input_lines = count($lines);

$total_output_line_counter = 0;

$output_counter = 0;

$file = null;

foreach ($lines as $line) {
	if ($output_counter === 0) {
		$file_counter += 1;
		$file_counter_string = sprintf("%04d", $file_counter);
		$filename = "migration_{$file_counter_string}_config.json";
		$file = fopen($filename, "w");
		fwrite($file, $preamble);
	}

	if ($output_counter < ($num_output_clients - 1) && $total_output_line_counter < ($total_input_lines - 1)) {
		$line .= ",";
	}

	$line = "        $line" .  PHP_EOL;

	fwrite($file, $line);

	$output_counter += 1;

	if ($output_counter == $num_output_clients || $total_output_line_counter == ($total_input_lines - 1)) {
		fwrite($file, $postamble);
		fclose($file);
		$file = null;
		$output_counter = 0;
	}

	$total_output_line_counter += 1;
}

echo "Wrote $file_counter config files" . PHP_EOL;

