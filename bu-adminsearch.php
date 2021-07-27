<?php
/**
 * Plugin Name: BU DBusers
 * Description: Search database for users based on capabilities
 */



    /**
     * Report on users
     * Author: dd
     * License: GPLv2 or later
     * License URI: https://www.gnu.org/licenses/gpl-2.0.html
     * Version: 0.1
     */
    class DBUserSearch {
        /**
         * Scans sites for a particular string.
         *
         * Scan by default for the 'administrator' capability, but will look for other capablility if entered in command.
         * Use WP_CLI command: wp dbsearch-user find "{a capability}" or leave blank to search for "administrator"
         * To push resuts to file: wp dbsearch-user find "{a capability}">> {your_file_name.txt}
         *
         * @param array $args Positional args.
         * @param array $args_assoc Assocative args.
         */
        public function find( $args, $args_assoc ) {

           
            $srchSrting = $args[0];
            //Set default search to 'administrator' if search arguments empty
            if($srchSrting == null){
                $srchSrting = 'administrator';
            }
            global $wpdb;
            $querycaps = "SELECT * FROM wp_usermeta WHERE meta_value LIKE '%".$srchSrting."%' AND meta_key LIKE '%capabilities'";
            $foundusers = $wpdb->get_results($querycaps);
            if ( ! $foundusers ) {
                \WP_CLI::error( 'No users found for '.$srchSrting );
            }

            // Setup a table to return the data.
            $output = new \cli\Table();
            $output->setHeaders(
                array(
                    'blog_id',
                    'url',
                    'blog_name',
                    'user_id',
                    'user_role',
                    'user_login',
                    'user_name',
                    'user_email'
                )
            );

         foreach ( $foundusers as $founduser ) {

            $user_ID = $founduser->user_id;
            $string = $founduser->meta_key;
            $pattern = '/wp_([0-9]+)_capabilities/';

            if($string == 'wp_capabilities'){

                // "wp_capabilities" metadata is for root site
                $blog_ID = 0;

            }else{

                // if other than "wp_capabilities", 
                // pull blod_Id from meta_key - "wp_{$blog_ID}_capabilities"
                preg_match($pattern, $string, $matches);
                $blog_ID = $matches[1];

            }

            // Get the blog data
            $theBlog = get_blog_details($blog_ID);
            $site_ID = $blog_ID;
            $site_url = $theBlog->home;
            $site_title = $theBlog->blogname;

            // Get the user data
            $theUser = get_user_by('id', $user_ID);
            $userName = $theUser->display_name;
            $userLogin = $theUser->user_login;
            $userEmail = $theUser->user_email;
            $userRole = $srchSrting;
                
                    // Add row of blog and user data to list
                    $row = array(
                        $blog_ID,
                        $site_url,
                        $site_title,
                        $user_ID,
                        $srchSrting,
                        $userLogin,
                        $userName,
                        $userEmail
                    );

                    // Push to output
                    $output->addRow( $row );
    
        }
        // Output when all is done and not before
        $output->display();
    }
}

    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        \WP_CLI::add_command( 'dbsearch-user', __CLASS__ . '\\DBUserSearch' );
    }
