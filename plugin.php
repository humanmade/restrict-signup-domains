<?php
/**
 * Plugin Name: Restrict Signup Domains
 * Description: Restrict signups to certain domains for single sites.
 * Author: Human Made
 * Author URI: https://humanmade.com/
 * Version: 1.0.0
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * Incorporates work from Restrict New Users by Domain by Michael Markoski
 *
 * Restrict Signup Domains is free software: you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Restrict Signup Domains is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Restrict Signup Domains. If not, see
 * https://www.gnu.org/licenses/gpl-2.0.html.
 */

namespace HM\RestrictSignupDomains;

const PLUGIN_FILE = __FILE__;

require __DIR__ . '/inc/namespace.php';
require __DIR__ . '/inc/admin/namespace.php';

bootstrap();
