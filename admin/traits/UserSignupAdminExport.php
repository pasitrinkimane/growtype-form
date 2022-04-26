<?php

trait UserSignupAdminExport
{
    /**
     * Set admin-related actions and filters.
     *
     * @since 2.0.0
     */
    function gf_admin_signup_export_data()
    {
        $action = $this->gt_admin_list_table_current_bulk_action();

        if ($action === 'export_selected' || $action === 'export_all') {

            $ids = [];
            if (!empty($_POST['allsignups'])) {
                $ids = wp_parse_id_list($_POST['allsignups']);
            } elseif (!empty($_GET['signup_id'])) {
                $ids = absint($_GET['signup_id']);
            }

            if (empty($ids) && $action !== 'export_all') {
                $redirect_to = add_query_arg([
                    'message' => 'No ids selected',
                    'page' => 'gf-signups',
                ], $this->users_url);

                header('Location: ' . $redirect_to);
                exit();
            }

            $orderby = 'registered';
            $order = 'order';

            if (isset($_POST['_wp_http_referer'])) {
                $url_parameters = parse_url($_POST['_wp_http_referer'])['query'];
                $url_parameters = explode('&', $url_parameters);

                foreach ($url_parameters as $param) {
                    if (str_contains($param, 'orderby=')) {
                        $orderby = str_replace('orderby=', '', $param);
                    } elseif (str_contains($param, 'order=')) {
                        $order = str_replace('order=', '', $param);
                    }
                }
            }

            $user_roles = !empty(get_option('growtype_form_default_user_role')) ? [get_option('growtype_form_default_user_role')] : ['subscriber'];

            $args = array (
                'orderby' => $orderby,
                'order' => $order,
                'include' => $ids,
                'role__in' => $user_roles,
            );

            if ($action === 'export_all') {
                $args['number'] = -1;
            }

            $export_args = [];

            date_default_timezone_set("Europe/Vilnius");
            $export_args['file_title'] = 'Registrations-' . date('Y-m-d-H-i');
            $export_args['file_type'] = 'csv';
            $export_args['file_content'] = $this->prepare_export_data($args, 'csv');

            return $this->export_users_signups($export_args);
        }
    }

    /**
     * This is the confirmation screen for actions.
     *
     * @param string $action Delete, activate, or resend activation link.
     *
     * @return null|false
     * @since 2.0.0
     *
     */
    public function export_users_signups($export_args)
    {
        $filename = $export_args['file_title'] . '.' . $export_args['file_type'];

        $fp = fopen($filename, 'w');

        foreach ($export_args['file_content'] as $line) {
            fputcsv($fp, $line);
        }

        fclose($fp);

        if ($export_args['file_type'] === 'csv') {
            header("Pragma: public");
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private", false);
            header("Content-Type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"" . $filename . "\";");
            header("Content-Transfer-Encoding: binary");
            header('Content-type: text/csv');
        }

        header('Content-disposition:attachment; filename="' . $filename . '"');
        readfile($filename);

        exit;
    }

    /**
     * Converting data to CSV
     */
    public function prepare_export_data($args, $type = 'csv')
    {
        $users = get_users($args);

        $export_fields = $this->prepare_export_fields($users);

        $export_data = [];
        $index = 0;

        foreach ($users as $user_key => $signup) {
            $signup_data = $this->get_user_data($signup->ID);

//            echo '<pre>' . var_export($signup_data, true) . '</pre>';
//            die();

            $profile_data_clean = [];
            foreach ($signup_data['signup_data'] as $field_key => $field_value) {
                $profile_data_clean[$field_key] = $field_value['value'];
            }

            $signup_details = array_merge((array)$signup_data['profile_data'], $profile_data_clean);

            foreach ($export_fields as $export_key => $field) {
                if ($index === 0) {
                    $export_data[$index][$export_key] = $field;
                    $export_data[$index + 1][$export_key] = isset($signup_details[$export_key]) ? $signup_details[$export_key] : '';
                } else {
                    $export_data[$index][$export_key] = isset($signup_details[$export_key]) ? $signup_details[$export_key] : '';
                }
            }

            if ($index === 0) {
                $index++;
                $index++;
            } else {
                $index++;
            }
        }

//        echo '<pre>' . var_export($export_data, true) . '</pre>';
//        die();

        return $export_data;
    }

    /**
     * @param $users
     * @return array
     */
    public function prepare_export_fields($users)
    {
        $export_fields = [];
        $export_fields['ID'] = 'ID';
        $export_fields['display_name'] = 'Username'; //user_nicename
        $export_fields['user_email'] = 'Email';
        $export_fields['user_registered'] = 'Registration date';

        foreach ($users as $key => $signup) {
            $signup_data = Growtype_Form_Signup::get_signup_data($signup->ID);

            foreach ($signup_data as $field_key => $field_value) {
                $export_fields[$field_key] = isset($field_value['label']) ? $field_value['label'] : $field_key;
            }
        }

        return $export_fields;
    }
}
