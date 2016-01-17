<?php
class aboutController {
    public function index (){
        $content  = "<p style align='center'>AEC Foyer Lataste CSS Management System";
        $content .= "<table width = '50%' id='collapsed'><col width='20%'><col width='15%'><col width='45%'><col width='20%'><tr><td>";
        $content .= "</td><td>";
        $content .= "<a href='http://ivanbragatto.byethost24.com'><img src='/css/images/logo_mouvement_blue_BG_black_IB_gray_EB_soft_upper_left_border.png' alt='IBweb' width='175px'></a>";
        $content .= "IBweb";
        $content .= "</td><td>";
        $content .= "<p>Programming language: PHP";
        $content .= "<p>Database: MySQL";
        $content .= "<p>";
        $content .= "<p>Only AEC-Foyer Lataste employees are authorized to use this Management System";
        $content .= "<p>Only the webmaster can and should make internal changes, otherwise he cannot be held accountable in case the system does not perform as expected";
        $content .= "<p>";
        $current_year = DATE('Y');
        $content .= "<p>Copyright {$current_year} - AEC Foyer Lataste";
        $content .= "</td><td></td></tr></table>";
        $output['content']=$content;
        return $output;
    }
}