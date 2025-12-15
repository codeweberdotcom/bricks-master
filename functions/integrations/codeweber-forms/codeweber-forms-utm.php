<?php
/**
 * CodeWeber Forms UTM Tracker
 * 
 * Collects and stores UTM parameters for form submissions
 * 
 * @package Codeweber
 */

if (!defined('ABSPATH')) {
    exit;
}

class CodeweberFormsUTM {
    /**
     * Get UTM parameters from request
     * 
     * @return array UTM parameters
     */
    public static function get_utm_params() {
        $utm_params = [];
        
        // Standard UTM parameters
        $utm_keys = [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'utm_id'
        ];
        
        // Get from GET parameters (current page)
        foreach ($utm_keys as $key) {
            if (isset($_GET[$key]) && !empty($_GET[$key])) {
                $utm_params[$key] = sanitize_text_field($_GET[$key]);
            }
        }
        
        // Get from POST parameters (if sent from frontend)
        if (isset($_POST['utm_params']) && is_array($_POST['utm_params'])) {
            foreach ($_POST['utm_params'] as $key => $value) {
                if (in_array($key, $utm_keys)) {
                    $utm_params[$key] = sanitize_text_field($value);
                }
            }
        }
        
        // Get from REST API request
        if (defined('REST_REQUEST') && REST_REQUEST) {
            $request = $_SERVER['REQUEST_URI'] ?? '';
            parse_str(parse_url($request, PHP_URL_QUERY), $query_params);
            
            foreach ($utm_keys as $key) {
                if (isset($query_params[$key]) && !empty($query_params[$key])) {
                    $utm_params[$key] = sanitize_text_field($query_params[$key]);
                }
            }
        }
        
        return $utm_params;
    }
    
    /**
     * Get additional tracking data
     * 
     * @return array Additional tracking data
     */
    public static function get_tracking_data() {
        $data = [];
        
        // Referrer
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $data['referrer'] = esc_url_raw($_SERVER['HTTP_REFERER']);
        }
        
        // Landing page
        if (isset($_POST['landing_page']) && !empty($_POST['landing_page'])) {
            $data['landing_page'] = esc_url_raw($_POST['landing_page']);
        } elseif (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $data['landing_page'] = esc_url_raw($_SERVER['HTTP_REFERER']);
        } else {
            $data['landing_page'] = home_url($_SERVER['REQUEST_URI'] ?? '');
        }
        
        // Get UTM params
        $utm_params = self::get_utm_params();
        if (!empty($utm_params)) {
            $data = array_merge($data, $utm_params);
        }
        
        return $data;
    }
    
    /**
     * Format UTM data for email display
     * 
     * @param array $utm_data UTM data
     * @return string Formatted HTML
     */
    public static function format_utm_for_email($utm_data) {
        if (empty($utm_data) || !is_array($utm_data)) {
            return '';
        }
        
        $html = '<h4 style="margin-top: 20px; margin-bottom: 10px;">UTM Parameters</h4>';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">';
        $html .= '<thead><tr style="background-color: #f5f5f5;"><th style="padding: 10px; text-align: left; border: 1px solid #ddd;">' . __('Parameter', 'codeweber') . '</th>';
        $html .= '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">' . __('Value', 'codeweber') . '</th></tr></thead>';
        $html .= '<tbody>';
        
        // UTM labels (always in English - technical terms)
        $labels = [
            'utm_source' => 'UTM Source',
            'utm_medium' => 'UTM Medium',
            'utm_campaign' => 'UTM Campaign',
            'utm_term' => 'UTM Term',
            'utm_content' => 'UTM Content',
            'utm_id' => 'UTM ID',
            'referrer' => 'Referrer',
            'landing_page' => 'Landing Page',
        ];
        
        foreach ($utm_data as $key => $value) {
            if (empty($value)) {
                continue;
            }
            
            $label = isset($labels[$key]) ? $labels[$key] : ucfirst(str_replace('_', ' ', $key));
            
            // For URLs, make them clickable
            $display_value = $value;
            if (in_array($key, ['referrer', 'landing_page']) && filter_var($value, FILTER_VALIDATE_URL)) {
                $display_value = '<a href="' . esc_url($value) . '" style="color: #0073aa; text-decoration: underline;">' . esc_html($value) . '</a>';
            } else {
                $display_value = esc_html($value);
            }
            
            $html .= '<tr>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd; font-weight: bold;">' . esc_html($label) . ':</td>';
            $html .= '<td style="padding: 10px; border: 1px solid #ddd;">' . $display_value . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';
        return $html;
    }
}

