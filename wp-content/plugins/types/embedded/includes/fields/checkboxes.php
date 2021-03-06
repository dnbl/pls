<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_checkboxes() {
    return array(
        'id' => 'wpcf-checkboxes',
        'title' => __('Checkboxes', 'wpcf'),
        'description' => __('Checkboxes', 'wpcf'),
//        'validate' => array('required'),
        'meta_key_type' => 'BINARY',
    );
}

/**
 * Form data for post edit page.
 * 
 * @param type $field 
 */
function wpcf_fields_checkboxes_meta_box_form($field, $data) {
    $options = array();
    if (!empty($field['data']['options'])) {
        global $pagenow;
        foreach ($field['data']['options'] as $option_key => $option) {
            // Set value
            $options[$option_key] = array(
                '#value' => $option['set_value'],
                '#title' => wpcf_translate('field ' . $field['id'] . ' checkbox '
                        . $option_key . ' title', $option['title']),
                '#default_value' => (!empty($data['#value'][$option_key])// Also check new post
                || ($pagenow == 'post-new.php' && !empty($option['checked']))) ? 1 : 0,
                '#name' => 'wpcf[' . $field['id'] . '][' . $option_key . ']',
                '#id' => $option_key . '_' . mt_rand(),
            );
        }
    }
    return array(
        '#type' => 'checkboxes',
        '#options' => $options,
    );
}

/**
 * Editor callback form.
 */
function wpcf_fields_checkboxes_editor_callback() {
    $form = array();
    $form['#form']['callback'] = 'wpcf_fields_checkboxes_editor_submit';
    $form['display'] = array(
        '#type' => 'radios',
        '#default_value' => 'db',
        '#name' => 'display',
        '#options' => array(
            'display_from_db' => array(
                '#title' => __('Display the value of this field from the database',
                        'wpcf'),
                '#name' => 'display',
                '#value' => 'db',
                '#inline' => true,
                '#after' => '<br />'
            ),
            'display_values' => array(
                '#title' => __('Show one of these two values:', 'wpcf'),
                '#name' => 'display',
                '#value' => 'value',
            ),
        ),
        '#inline' => true,
    );
    if (isset($_GET['field_id'])) {
        $field = wpcf_admin_fields_get_field($_GET['field_id']);
        if (!empty($field['data']['options'])) {
            foreach ($field['data']['options'] as $option_key => $option) {
                $form[$option_key . '-markup'] = array(
                    '#type' => 'markup',
                    '#markup' => '<h3>' . $option['title'] . '</h3>',
                );
                $form[$option_key . '-display-value-1'] = array(
                    '#type' => 'textfield',
                    '#title' => '<td style="text-align:right;">'
                    . __('Not selected:', 'wpcf') . '</td><td>',
                    '#name' => 'options[' . $option_key . '][display_value_not_selected]',
                    '#value' => $option['display_value_not_selected'],
                    '#inline' => true,
                    '#before' => '<table><tr>',
                    '#after' => '</td></tr>',
                );
                $form[$option_key . '-display-value-2'] = array(
                    '#type' => 'textfield',
                    '#title' => '<td style="text-align:right;">'
                    . __('Selected:', 'wpcf') . '</td><td>',
                    '#name' => 'options[' . $option_key . '][display_value_selected]',
                    '#value' => $option['display_value_selected'],
                    '#after' => '</tr></table>'
                );
            }
        }
    }
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => __('Save Changes'),
        '#attributes' => array('class' => 'button-primary'),
    );
    $f = wpcf_form('wpcf-form', $form);
    wpcf_admin_ajax_head('Insert checkbox', 'wpcf');
    echo '<form method="post" action="">';
    echo $f->renderForm();
    echo '</form>';
    wpcf_admin_ajax_footer();
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_checkboxes_editor_submit() {
    $add = '';
    $field = wpcf_admin_fields_get_field($_GET['field_id']);
    $shortcode = '';
    if (!empty($field)) {
        if (!empty($_POST['options'])) {
            $i = 0;
            foreach ($_POST['options'] as $option_key => $option) {
                if ($_POST['display'] == 'value') {

                    $shortcode .= '[types field="' . $field['slug'] . '" option="'
                            . $i . '" state="checked"]'
                            . $option['display_value_selected']
                            . '[/types] ';
                    $shortcode .= '[types field="' . $field['slug'] . '" option="'
                            . $i . '" state="unchecked"]'
                            . $option['display_value_not_selected']
                            . '[/types] ';
                } else {
                    $add = ' option="' . $i . '"';
                    $shortcode .= wpcf_fields_get_shortcode($field, $add) . ' ';
                }
                $i++;
            }
        }
        echo wpcf_admin_fields_popup_insert_shortcode_js($shortcode);
        die();
    }
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_checkboxes_view($params) {
    $option = array();
    if (!isset($params['option']) || empty($params['field']['data']['options'])) {
        return '__wpcf_skip_empty';
    }

    $i = 0;
    foreach ($params['field']['data']['options'] as $option_key => $option_value) {
        if (intval($params['option']) == $i) {
            $option['key'] = $option_key;
            $option['data'] = $option_value;
            $option['value'] = isset($params['field_value'][$option_key]) ? $params['field_value'][$option_key] : '__wpcf_unchecked';
            break;
        }
        $i++;
    }

    $output = '';
    if (isset($params['state']) && $params['state'] == 'unchecked' && $option['value'] == '__wpcf_unchecked') {
        return htmlspecialchars_decode($params['#content']);
    } else if (isset($params['state']) && $params['state'] == 'unchecked') {
        return '__wpcf_skip_empty';
    }

    if (isset($params['state']) && $params['state'] == 'checked' && $option['value'] != '__wpcf_unchecked') {
        return htmlspecialchars_decode($params['#content']);
    } else if (isset($params['state']) && $params['state'] == 'checked') {
        return '__wpcf_skip_empty';
    }
//    if (!empty($params['#content'])) {
//        return htmlspecialchars_decode($params['#content']);
//    }

    if ($option['data']['display'] == 'db'
            && !empty($option['data']['set_value']) && $option['value'] != '__wpcf_unchecked') {
        $output = $option['data']['set_value'];
        $output = wpcf_translate('field ' . $params['field']['id'] . ' checkbox value',
                $output);
    } else if ($option['data']['display'] == 'value'
            && $option['value'] != '__wpcf_unchecked') {
        if (isset($option['data']['display_value_selected'])) {
            $output = $option['data']['display_value_selected'];
            $output = wpcf_translate('field ' . $params['field']['id'] . ' checkbox value selected',
                    $output);
        }
    } else if ($option['data']['display'] == 'value') {
        if (isset($option['data']['display_value_not_selected'])) {
            $output = $option['data']['display_value_not_selected'];
            $output = wpcf_translate('field ' . $params['field']['id'] . ' checkbox value not selected',
                    $output);
        }
    }

    if (empty($output)) {
        return '__wpcf_skip_empty';
    }

    return $output;
}