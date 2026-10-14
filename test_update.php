<?php
$payload = [
    '_method' => 'PUT',
    '_token' => 'dummy', // Will fail CSRF unless disabled
];
// Just using tinker to directly call the controller method is easier.
