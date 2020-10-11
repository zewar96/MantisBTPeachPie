<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Display Project Changelog
 *
 * @package MantisBT
 * @copyright Copyright 2000 - 2002  Kenzaburo Ito - kenito@300baud.org
 * @copyright Copyright 2002  MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link http://www.mantisbt.org
 *
 * @uses core.php
 * @uses access_api.php
 * @uses authentication_api.php
 * @uses bug_api.php
 * @uses category_api.php
 * @uses config_api.php
 * @uses constant_inc.php
 * @uses database_api.php
 * @uses error_api.php
 * @uses filter_api.php
 * @uses filter_constants_inc.php
 * @uses gpc_api.php
 * @uses helper_api.php
 * @uses html_api.php
 * @uses lang_api.php
 * @uses print_api.php
 * @uses project_api.php
 * @uses string_api.php
 * @uses user_api.php
 * @uses utility_api.php
 * @uses version_api.php
 */

require_once( 'core.php' );
require_api( 'access_api.php' );
require_api( 'authentication_api.php' );
require_api( 'bug_api.php' );
require_api( 'category_api.php' );
require_api( 'config_api.php' );
require_api( 'constant_inc.php' );
require_api( 'database_api.php' );
require_api( 'error_api.php' );
require_api( 'filter_api.php' );
require_api( 'filter_constants_inc.php' );
require_api( 'gpc_api.php' );
require_api( 'helper_api.php' );
require_api( 'html_api.php' );
require_api( 'lang_api.php' );
require_api( 'print_api.php' );
require_api( 'project_api.php' );
require_api( 'string_api.php' );
require_api( 'user_api.php' );
require_api( 'utility_api.php' );
require_api( 'version_api.php' );

/**
 * Print header for the specified project version.
 * @param integer $p_version_id A valid version identifier.
 * @return void
 */
function print_version_header( $p_version_id ) {
	$t_project_id   = version_get_field( $p_version_id, 'project_id' );
	$t_version_name = version_get_field( $p_version_id, 'version' );
	$t_project_name = project_get_field( $t_project_id, 'name' );

	$t_release_title = '<a class="white" href="changelog_page.php?project_id=' . $t_project_id . '">' . string_display_line( $t_project_name ) . '</a>';
	$t_release_title .= ' - <a class="white" href="changelog_page.php?version_id=' . $p_version_id . '">' . string_display_line( $t_version_name ) . '</a>';

	if( config_get( 'show_changelog_dates' ) ) {
		$t_version_released = version_get_field( $p_version_id, 'released' );
		$t_release_timestamp = version_get_field( $p_version_id, 'date_order' );

		if( (bool)$t_version_released ) {
			$t_release_date = lang_get( 'released' ) . ' ' . string_display_line( date( config_get( 'short_date_format' ), $t_release_timestamp ) );
		} else {
			$t_release_date = lang_get( 'not_released' );
		}
	} else {
		$t_release_date = '';
	}

	$t_block_id = 'changelog_' . $p_version_id;
	$t_collapse_block = is_collapsed( $t_block_id );
	$t_block_css = $t_collapse_block ? 'collapsed' : '';
	$t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

	echo '<div id="' . $t_block_id . '" class="widget-box widget-color-blue2 ' . $t_block_css . '">';
	echo '<div class="widget-header widget-header-small">';
	echo '<h4 class="widget-title lighter">';
	echo '<i class="ace-icon fa fa-retweet"></i>';
	echo $t_release_title, lang_get( 'word_separator' );
	echo '</h4>';
	echo '<div class="widget-toolbar">';
	echo '<a data-action="collapse" href="#">';
	echo '<i class="1 ace-icon fa ' . $t_block_icon . ' bigger-125"></i>';
	echo '</a>';
	echo '</div>';
	echo '</div>';

	echo '<div class="widget-body">';
	echo '<div class="widget-toolbox padding-8 clearfix">';
	echo '<div class="pull-left"><i class="fa fa-calendar-o fa-lg"> </i> ' . $t_release_date . '</div>';
	echo '<div class="btn-toolbar pull-right">';
	echo '<a class="btn btn-xs btn-primary btn-white btn-round" ';
	echo 'href="view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW . '&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $t_project_id .
		 '&' . filter_encode_field_and_value( FILTER_PROPERTY_FIXED_IN_VERSION, $t_version_name ) .
		 '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">';
	echo lang_get( 'view_bugs_link' );
	echo '<a class="btn btn-xs btn-primary btn-white btn-round" href="changelog_page.php?version_id=' . $p_version_id . '">' . string_display_line( $t_version_name ) . '</a>';
	echo '<a class="btn btn-xs btn-primary btn-white btn-round" href="changelog_page.php?project_id=' . $t_project_id . '">' . string_display_line( $t_project_name ) . '</a>';
	echo '</a>';
	echo '</div>';

	echo '</div>';
	echo '<div class="widget-main">';
}

/**
 * Print footer for the specified project version.
 * @param int $p_version_id a valid version id
 * @param int $p_issues_resolved number of issues in resolved state
 * @return void
 */
function print_version_footer( $p_version_id, $p_issues_resolved ) {
	$t_project_id   = version_get_field( $p_version_id, 'project_id' );
	$t_bug_string = $p_issues_resolved == 1 ? 'bug' : 'bugs';
	$t_version_name = version_get_field( $p_version_id, 'version' );

	echo '</div>';
	echo '<div class="widget-toolbox padding-8 clearfix">';
	echo ' ' . $p_issues_resolved . ' ' . lang_get( $t_bug_string ) . ' ';
	echo '<a class="btn btn-xs btn-primary btn-white btn-round" ';
	echo 'href="view_all_set.php?type=' . FILTER_ACTION_PARSE_NEW . '&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $t_project_id .
		 '&' . filter_encode_field_and_value( FILTER_PROPERTY_FIXED_IN_VERSION, $t_version_name ) .
		 '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">';
	echo lang_get( 'view_bugs_link' );
	echo '</a>';
	echo '</div></div></div>';
	echo '<div class="space-10"></div>';
}

/**
 * Print header for the specified project
 * @param string $p_project_name Project name to display.
 * @return void
 */
function print_project_header_changelog( $p_project_name ) {
	echo '<div class="page-header">';
	echo '<h1><strong>' . string_display_line( $p_project_name ), '</strong> - ', lang_get( 'changelog' ) . '</h1>';
	echo '</div>';
}

$t_issues_found = false;
$t_user_id = auth_get_current_user_id();

$f_project = gpc_get_string( 'project', '' );
if( is_blank( $f_project ) ) {
	$f_project_id = gpc_get_int( 'project_id', -1 );
} else {
	$f_project_id = project_get_id_by_name( $f_project );

	if( $f_project_id === 0 ) {
		error_parameters( $f_project );
		trigger_error( ERROR_PROJECT_NOT_FOUND, ERROR );
	}
}

$f_version = gpc_get_string( 'version', '' );

if( is_blank( $f_version ) ) {
	$f_version_id = gpc_get_int( 'version_id', -1 );

	# If both version_id and project_id parameters are supplied, then version_id take precedence.
	if( $f_version_id == -1 ) {
		if( $f_project_id == -1 ) {
			$t_project_id = helper_get_current_project();
		} else {
			$t_project_id = $f_project_id;
		}
	} else {
		$t_project_id = version_get_field( $f_version_id, 'project_id' );
	}
} else {
	if( $f_project_id == -1 ) {
		$t_project_id = helper_get_current_project();
	} else {
		$t_project_id = $f_project_id;
	}

	$f_version_id = version_get_id( $f_version, $t_project_id );

	if( $f_version_id === false ) {
		error_parameters( $f_version );
		trigger_error( ERROR_VERSION_NOT_FOUND, ERROR );
	}
}

if( ALL_PROJECTS == $t_project_id ) {
	$t_project_ids_to_check = user_get_all_accessible_projects( $t_user_id, ALL_PROJECTS );
	$t_project_ids = array();

	foreach ( $t_project_ids_to_check as $t_project_id ) {
		$t_changelog_view_access_level = config_get( 'view_changelog_threshold', null, null, $t_project_id );
		if( access_has_project_level( $t_changelog_view_access_level, $t_project_id ) ) {
			$t_project_ids[] = $t_project_id;
		}
	}
} else {
	access_ensure_project_level( config_get( 'view_changelog_threshold' ), $t_project_id );
	$t_project_ids = user_get_all_accessible_subprojects( $t_user_id, $t_project_id );
	array_unshift( $t_project_ids, $t_project_id );
}

$t_project_id_for_access_check = $t_project_id;

layout_page_header( lang_get( 'changelog' ) );

layout_page_begin( __FILE__ );

echo '<div class="col-md-12 col-xs-12">';

$t_project_index = 0;

version_cache_array_rows( $t_project_ids );
category_cache_array_rows_by_project( $t_project_ids );

foreach( $t_project_ids as $t_project_id ) {
	$t_project_name = project_get_field( $t_project_id, 'name' );
	$t_resolved = config_get( 'bug_resolved_status_threshold' );

	# grab versions info for later use, excluding obsolete ones
	$t_version_rows = version_get_all_rows( $t_project_id, VERSION_ALL, false );

	# cache category info, but ignore the results for now
	category_get_all_rows( $t_project_id );

	$t_project_header_printed = false;

	$t_view_bug_threshold = config_get( 'view_bug_threshold', null, $t_user_id, $t_project_id );

	foreach( $t_version_rows as $t_version_row ) {
		$t_version_header_printed = false;

		$t_version = $t_version_row['version'];
		$t_version_id = $t_version_row['id'];

		# Skip all versions except the specified one (if any).
		if( $f_version_id != -1 && $f_version_id != $t_version_id ) {
			continue;
		}

		$t_query = 'SELECT sbt.*, dbt.fixed_in_version AS parent_version, rt.source_bug_id
			FROM {bug} sbt
			LEFT JOIN {bug_relationship} rt
				ON sbt.id=rt.destination_bug_id AND rt.relationship_type=' . BUG_DEPENDANT . '
			LEFT JOIN {bug} dbt ON dbt.id=rt.source_bug_id
			WHERE sbt.project_id=' . db_param() . '
			  AND sbt.fixed_in_version=' . db_param() . '
			ORDER BY sbt.status ASC, sbt.last_updated DESC';

		$t_description = version_get_field( $t_version_id, 'description' );

		$t_first_entry = true;
		$t_issue_ids = array();
		$t_issue_parents = array();
		$t_issue_handlers = array();

		$t_result = db_query( $t_query, array( $t_project_id, $t_version ) );

		while( $t_row = db_fetch_array( $t_result ) ) {
			bug_cache_database_result( $t_row );

			# verify the user can view this issue
			if( !access_has_bug_level( $t_view_bug_threshold, $t_row['id'] ) ) {
				continue;
			}

			$t_issue_id = $t_row['id'];
			$t_issue_parent = $t_row['source_bug_id'];
			$t_parent_version = $t_row['parent_version'];

			if( !helper_call_custom_function( 'changelog_include_issue', array( $t_issue_id ) ) ) {
				continue;
			}

			if( 0 === strcasecmp( $t_parent_version, $t_version ) ) {
				$t_issue_ids[] = $t_issue_id;
				$t_issue_parents[] = $t_issue_parent;
			} else if( !in_array( $t_issue_id, $t_issue_ids ) ) {
				$t_issue_ids[] = $t_issue_id;
				$t_issue_parents[] = null;
			}

			$t_issue_handlers[] = $t_row['handler_id'];
		}

		user_cache_array_rows( array_unique( $t_issue_handlers ) );

		$t_issues_resolved = count( array_unique( $t_issue_ids ) );

		if( $t_issues_resolved > 0 ) {
			if( !$t_project_header_printed ) {
				print_project_header_changelog( $t_project_name );
				$t_project_header_printed = true;
			}

			if( !$t_version_header_printed ) {
				print_version_header( $t_version_id );
				$t_version_header_printed = true;
			}

			if( !is_blank( $t_description ) ) {
				echo '<div class="alert alert-warning">', string_display( "$t_description" ), '</div>';
			}
		} else {
			continue;
		}

		$t_issue_set_ids = array();
		$t_issue_set_levels = array();
		$k = 0;

		$t_cycle = false;
		$t_cycle_ids = array();

		while( !empty( $t_issue_ids ) ) {
			$t_issue_id = $t_issue_ids[$k];
			$t_issue_parent = $t_issue_parents[$k];

			if( in_array( $t_issue_id, $t_cycle_ids ) && in_array( $t_issue_parent, $t_cycle_ids ) ) {
				$t_cycle = true;
			} else {
				$t_cycle = false;
				$t_cycle_ids[] = $t_issue_id;
			}

			if( $t_cycle || !in_array( $t_issue_parent, $t_issue_ids ) ) {
				$l = array_search( $t_issue_parent, $t_issue_set_ids );
				if( $l !== false ) {
					for( $m = $l+1; $m < count( $t_issue_set_ids ) && $t_issue_set_levels[$m] > $t_issue_set_levels[$l]; $m++ ) {
						#do nothing
					}
					$t_issue_set_ids_end = array_splice( $t_issue_set_ids, $m );
					$t_issue_set_levels_end = array_splice( $t_issue_set_levels, $m );
					$t_issue_set_ids[] = $t_issue_id;
					$t_issue_set_levels[] = $t_issue_set_levels[$l] + 1;
					$t_issue_set_ids = array_merge( $t_issue_set_ids, $t_issue_set_ids_end );
					$t_issue_set_levels = array_merge( $t_issue_set_levels, $t_issue_set_levels_end );
				} else {
					$t_issue_set_ids[] = $t_issue_id;
					$t_issue_set_levels[] = 0;
				}
				array_splice( $t_issue_ids, $k, 1 );
				array_splice( $t_issue_parents, $k, 1 );

				$t_cycle_ids = array();
			} else {
				$k++;
			}
			if( count( $t_issue_ids ) <= $k ) {
				$k = 0;
			}
		}

		for( $j = 0; $j < count( $t_issue_set_ids ); $j++ ) {
			$t_issue_set_id = $t_issue_set_ids[$j];
			$t_issue_set_level = $t_issue_set_levels[$j];

			helper_call_custom_function( 'changelog_print_issue', array( $t_issue_set_id, $t_issue_set_level ) );

			$t_issues_found = true;
		}

	if( $t_version_header_printed ) {
		print_version_footer( $t_version_id,  $t_issues_resolved);
	}
}

	$t_project_index++;
}

if( !$t_issues_found ) {
	if( access_has_project_level( config_get( 'manage_project_threshold' ), $t_project_id_for_access_check ) ) {
		$t_string = 'changelog_empty_manager';
	} else {
		$t_string = 'changelog_empty';
	}

	echo '<br />';
	echo '<p class="lead">' . lang_get( $t_string ) . '</p>';
}
echo '</div>';
layout_page_end();
