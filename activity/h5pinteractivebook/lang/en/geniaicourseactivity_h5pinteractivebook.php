<?php
$string['pluginname'] = 'H5P Interactive Book';
$string['description'] = 'Creates an H5P Interactive Book and can reuse selected H5P subplugins as book pages. Select the book together with one or more H5P types to place those types inside it; select only the book to use the H5P types recommended by the analysis.';
$string['h5pbookembedunsupported'] = 'H5P Interactive Book cannot embed {$a} with the H5P.Column libraries installed on this Moodle site.';
$string['h5pbookchildfailed'] = '{$a->name} could not be added to the Interactive Book: {$a->error}';
$string['h5pbookiframewarning'] = 'These H5P types are embedded in the book through an internal iframe because the installed H5P.Column library does not accept them as direct children: {$a}. Their own interaction score is not aggregated into the Interactive Book summary.';
$string['h5pbookpagedefault'] = 'Book page {$a}';
$string['h5plibrarymissing'] = 'The required H5P library is not installed or enabled in Moodle: {$a}';
$string['h5pcontentcreatefailed'] = 'Moodle could not build the H5P content. {$a}';
$string['h5pexportmissing'] = 'The H5P editor did not generate an export package for the activity.';
$string['h5pinvalidcontent'] = 'The generated content is not sufficient to create {$a}.';
