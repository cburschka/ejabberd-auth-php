<?php

/**
 * config.php
 *
 * Configure the Bridge plugins used by this authentication system.
 *
 * Example 1: Use a Drupal 8 site for all hosts.
 *
 * $config['*'][0] = [
 *   'plugin' => 'drupal8',
 *   'config' => [
 *     'root_path' => '/path/to/drupal8',
 *     'site' => 'default',
 *   ],
 * ];
 *
 * Example 2: Add a phpBB and MediaWiki subdomain (exact match):
 *
 * $config['forum.example.com'][0] = [
 *   'plugin' => 'phpbb30',
 *   'config => ['root_path' => '/path/to/phpbb'],
 * ];
 * $config['wiki.example.com'][0] = [
 *   'plugin' => 'mediawiki',
 *   'config' => ['root_path' => '/path/to/mediawiki'],
 * ];
 *
 * Example 3: Allow session authentication (see plugins/session/README.md)
 *
 * $config['*'][0] = [
 *   'plugin' => 'phpbb30',
 *   'config => ['root_path' => '/path/to/phpbb'],
 * ];
 * $config['*'][1] = [
 *   'plugin' => 'session',
 *   'config' => [
 *     'mysql' => [
 *       'dsn' => 'mysql:host=localhost;dbname=DATABASE;charset=utf8',
 *       'username' => 'USER',
 *       'password' => 'PASSWORD',
 *       'table' => 'TABLE',
 *     ],
 *     'plugin' => 'phpbb30'
 *   ],
 * ];
 */

$config['*'][0] = [
  'plugin' => '',
  'config' => [
    'root_path' => '',
  ],
];
