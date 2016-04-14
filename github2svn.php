<?php

/*
Plugin Name: Github2SVN
Author: Foliovision
Version: 0.1
*/


class Github2wpsvn {


	function __construct() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}
  
  
  function check_already_tagged($arr_output) {
    foreach($arr_output as $aoKey) {
      if( preg_match('/alreadytagged/', $aoKey) ) {
        return explode(':',$aoKey);
      } 
    } 
    return false;
  }
  
  
  function check_committed($arr_output) {
    foreach($arr_output as $aoKey) {
      if( preg_match('/svn_output_commit/', $aoKey) ) {
        return true;
      }
    } 
    return false;
  }
  
  
  function check_git() {
    exec( "git --version | sed -e 's!^!v:!g'", $arr_output );
  
    if( !empty($arr_output) ) {
      $res = explode(":", $arr_output[0]);
      if( preg_match('/git version/', $res[1]) ) {
        return true;
      }
    } 
    
    return false;
  }

  
  function check_tagged($arr_output) {
    foreach($arr_output as $aoKey) {
      if( preg_match('/currenttagged/', $aoKey) ) {
        return explode(':', $aoKey);
      } 
    } 
    return false;
  }
  
	
	function menu() {
    add_menu_page( 'Github2SVN', 'Github2SVN', 'manage_options', 'github2wpsvn', array( $this, 'screen' )	);
	}
  
	
	function screen() {    
		if( isset($_POST['github2svn_add']) && wp_verify_nonce($_POST['github2svn_add'],'github2svn_add') ) {
			if( $_POST['plugin_name'] != '' && $_POST['plugin_svn_slug'] != '' && $_POST['plugin_github'] ) {
				update_option('plugin_name_'.$_POST['plugin_svn_slug'], $_POST['plugin_name'] . '|' .$_POST['plugin_svn_slug'] . '|' . $_POST['plugin_github'] . '|' . $_POST['plugin_main_php_file']);        
        echo "<div class='updated'><p>Plugin info updated</p></div>";
			} else {
        echo "<div class='error'><p>No plugin info entered</p></div>";
      }
      
    } else if( isset($_POST['github2svn_commit']) && isset($_POST['commit_plugin_svn_slug']) && wp_verify_nonce($_POST['github2svn_commit'],'github2svn_commit') ) {

      $git_installed = $this->check_git(); 
      if( !$git_installed ) {
        echo "<div class='error'><p>Git not found or not able to execute if with PHP exec().</p></div>";
        
      } else if( empty($_POST['commit_plugin_svn_slug']) ) {
        echo "<div class='error'><p>Please select some plugin.</p></div>";
        
      } else if( empty($_POST['commitmessage']) || trim($_POST['commitmessage']) == '' ) {
        echo "<div class='error'><p>Please enter the commit message.</p></div>";
        
      } else if ( !empty($_POST['commitmessage']) && !empty($_POST['commit_plugin_svn_slug']) ) {

        $plugin_data = explode('|', $_POST['commit_plugin_svn_slug']);
        $plugin_name = $plugin_data[0];
        $plugin_slug = $plugin_data[1];
        $github_repo = $plugin_data[2];
        $plugin_main_php_file = $plugin_data[3];
        $username = 'USERNAME';
        $password = 'PASSWORD';
        
        $gittosvn_dir = ABSPATH.'github2svn';
        if (!is_dir($gittosvn_dir)) {
          mkdir($gittosvn_dir);
        }
        if (!is_dir($gittosvn_dir)) {
          die("Can't create the directory: ".$gittosvn_dir);
        }
        
        $commit_msg = ( $_POST['commitmessage'] ) ? trim($_POST['commitmessage']) : 'commit message';
        // $commit_msg = escapeshellarg($commit_msg);

        $shellfile = ($_POST['tagcurrent'] == 'on' ) ? 'tagcurrent' : 'commitnotag';
        $bash = ABSPATH.'wp-content/plugins/github2svn/'. $shellfile . '.sh';
        
        ini_set('max_execution_time', 20000);
        $res = exec( "$bash $plugin_slug $gittosvn_dir $github_repo $username $password '$commit_msg' '$plugin_name', $plugin_main_php_file 2>&1", $arr_output );
        
        echo "<pre>";
        echo "Executing: $bash $plugin_slug $gittosvn_dir $github_repo $username $password '$commit_msg' '$plugin_name', $plugin_main_php_file\n\n";
        echo implode("\n",$arr_output);
        echo "</pre>";
        die();  //  for debug
        
        $committed_msg = 'Nothing to commit.';
        if( $this->committed($arr_output) ) {
          $committed_msg = 'Changes successfully committed to SVN repo';
        }
        // if ($at = $this->check_already_tagged($arr_output)) {
        // 	$committed_msg = 'Current version: ' . $at[1]  .' already tagged';
        // }
        if ($ct = $this->check_tagged($arr_output)) {
          $committed_msg = 'Successfully tagged current version: ' . $ct[1] . ' ';
        }

        setcookie('msg[committed_msg]', "<b style='color:red;'>$committed_msg</b><br />", time() + 300);
        
      }
      
		}


		?>
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br></div>
			<h2>Github to WordPress.org SVN</h2> 

      <div class="plugin_version_control_wrap">
        <?php 
        global $wpdb;
        $plugins = $wpdb->get_results( "SELECT * FROM {$wpdb->options} WHERE option_name LIKE '%plugin_name_%' ");
        if (count($plugins)) : ?>
          <form id="commit_<?php echo $plugin_slug; ?>" method="POST" action="<?php echo site_url('wp-admin/admin.php?page=github2wpsvn'); ?>">	
            <table class="wp-list-table widefat fixed striped posts">
              <thead>
                <tr>
                  <th>Plugin name</th><th style="width: 200px">Do not commit, but tag</th><th>Commit message</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>
                    <select style="width: 100%" name="commit_plugin_svn_slug" id="commit_plugin_svn_slug">
                      <option value="">please choose a plugin</option>
                      <?php foreach($plugins as $plugin) {
                        if ($plugin->option_value != '' && $plugin->option_value != '|') { 
                        
                        $repos = explode('|', $plugin->option_value);
                        $plugin_name = ( $repos[0] ) ? $repos[0] : '';
                        $plugin_slug = ( $repos[1] ) ? $repos[1] : '';
                        $github_repo = ( $repos[2] ) ? $repos[2] : ''; 
                        $main_php_file = ( $repos[3] ) ? $repos[3] : '';
                        ?>
                          <option value="<?php echo "$plugin_name|$plugin_slug|$github_repo|$main_php_file"; ?>"><?php echo $plugin_name; ?></option>
                        <?php
                        } 
                      } ?>
                    </select>
                  </td>
                  <td>
                    <input style="float:left;" type="checkbox" name="tagcurrent" id="tagcurrent">
                  </td>
                  <td>
                    <textarea class="large-text" name="commitmessage" id="commitmessage" rows="1" placeholder="Don't forget about commit message"></textarea>
                  </td>             
                </tr>
              </tbody>
            </table>
            <p><input type="submit" class="button-primary alignright" value="Commit"><div style="clear:both"></div></p>
            <?php wp_nonce_field( 'github2svn_commit', 'github2svn_commit' ); ?>
          </form>
        <?php endif; ?>
      </div>
      
      <style>
        #update_plugin_list input[type=text] { width: 100% }
      </style>
      
      <div id="plugin_wrap">
        <form action="<?php echo site_url('wp-admin/admin.php?page=github2wpsvn'); ?>" id="update_plugin_list" name="update_plugin_list" method="POST">
          <table class="wp-list-table widefat fixed striped posts">
            <thead>
              <tr>
                <th>Plugin name</th><th style="width: 230px">WordPress.org SVN repository slug</th><th>Github .git path</th><th>Main plugin PHP file name</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input type="text" name="plugin_name" id="plugin_name" placeholder="My Awesome Plugin" /></td>
                <td><input type="text" name="plugin_svn_slug" id="plugin_svn_slug" placeholder="my-awesome-plugin" /></td>
                <td><input type="text" name="plugin_github" id="plugin_github" placeholder="https://github.com/my-account/my-awesome-plugin.git" /></td>
                <td><input type="text" name="plugin_main_php_file" id="plugin_main_php_file" placeholder="plugin.php" /></td>
              </tr>
            </tbody>
          </table>
          <p><input type="submit" class="button-primary alignright" value="Add Plugin"></p>
          <?php wp_nonce_field( 'github2svn_add', 'github2svn_add' ); ?>
        </form>
      </div>

      <div id="results_wrap"><div>
      <script type="text/javascript">
      (function( $ ) {
        $(document).ready(function() {
          var addPluginSubmit = $('#plugin_wrap > input[type="submit"]'),
          pluginFields = $('#plugin_wrap').find('input[type="text"]');
          
          function checkPluginFields() {
            jQuery.each(pluginFields, function(k, v) {
              if ($(v).val() === '') {
                addPluginSubmit.attr('disabled', 'disabled');
              } else {
                addPluginSubmit.removeAttr('disabled');
              }
            });
          }
          
          pluginFields.on('keyup', function() {
            checkPluginFields();
          });
          
          checkPluginFields();
          
          
          $('select#commit_plugin_svn_slug').on('change', function() {
            var pluginName = $(this).val();
            if (pluginName != '') { 
              var pluginName = pluginName.split('|');
              jQuery('input#plugin_name').val(pluginName[0]);
              jQuery('input#plugin_svn_slug').val(pluginName[1]);
              jQuery('input#plugin_github').val(pluginName[2]);
              jQuery('input#plugin_main_php_file').val(pluginName[3]);
              jQuery('#plugin_wrap > input[type="submit"]').val('Update Plugin');
            
            } else {
              jQuery.each(pluginFields, function(k, v) {
                if ($(v).attr('type') !== 'submit') {
                  $(v).val('');
                } else {
                  $(v).val('Add Plugin');
                }
              });
              
            }
            checkPluginFields();
          });
          
        });
      
      })( jQuery );
      </script> 
    
    </div>
	<?php
  }
	
}


$Github2wpsvn = new Github2wpsvn;
