<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Content Box block
 *
 * @package    block_tb_top_cats
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This block simply outputs the Top Categories.
 *
 * @copyright  2020 Leeloo LXP (https://leeloolxp.com)
 * @author     Leeloo LXP <info@leeloolxp.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_tb_top_cats extends block_base {

    /**
     * Initialize.
     *
     */
    public function init() {
        $this->title = get_string('pluginname', 'block_tb_top_cats');
    }

    /**
     * Return contents of tb_top_cats block
     *
     * @return stdClass contents of block
     */
    public function get_content() {

        if ($this->content !== null) {
            return $this->content;
        }

        $leeloolxplicense = get_config('block_tb_top_cats')->license;

        $url = 'https://leeloolxp.com/api_moodle.php/?action=page_info';
        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_tb_top_cats');
            return $this->content;
        }

        $infoleeloolxp = json_decode($output);

        if ($infoleeloolxp->status != 'false') {
            $leeloolxpurl = $infoleeloolxp->data->install_url;
        } else {
            $this->content->text = get_string('nolicense', 'block_tb_top_cats');
            return $this->content;
        }

        $url = $leeloolxpurl . '/admin/Theme_setup/get_categories_data';

        $postdata = '&license_key=' . $leeloolxplicense;

        $curl = new curl;

        $options = array(
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => false,
            'CURLOPT_POST' => 1,
        );

        if (!$output = $curl->post($url, $postdata, $options)) {
            $this->content->text = get_string('nolicense', 'block_tb_top_cats');
            return $this->content;
        }

        $resposedata = json_decode($output);

        $topcats = $resposedata->data->categories_data;

        if (empty($resposedata->data->block_title)) {
            $resposedata->data->block_title = get_string('displayname', 'block_tb_top_cats');
        }
        $this->title = $resposedata->data->block_title;

        $this->content = new stdClass();
        $this->content->text = '<div class="tb_top_cats">';

        foreach($topcats as $cat){
            $this->content->text .= '<div class="tb_cat">';

            if($cat->image != ''){
                $catimage = '<img src="'.$cat->image.'"/>';
            }else{
                $catimage = '';
            }

            $this->content->text .= '<div class="tb_cat_img">'.$catimage.'</div>';
            
            $this->content->text .= '<div class="tb_cat_content">';

            $this->content->text .= '<h2 class="cat_title"><a href="'.$cat->link.'">'.$cat->theme_title.'</a></h2>';

            $this->content->text .= '<p class="cat_des">'.$cat->description.'</p>';

            $this->content->text .= '</div>';

            $this->content->text .= '</div>';
        }

        $this->content->text .= '</div>';

        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('all' => true);
    }
}
