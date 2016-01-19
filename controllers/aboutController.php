<?php
class aboutController {
    public function index (){

        $content  = "<div class = 'about'>";
        $content .= "<div class = 'logo'>";
        $content .= "<a href='http://ivanbragatto.byethost24.com'><img src='/css/images/logo_mouvement_blue_BG_black_IB_gray_EB_soft_upper_left_border.png' alt='IBweb' width='175px'></a>";
        $content .= "</div>";

        $content .= "<div class = 'message'>";
        $content .= "<p><h2>AEC Foyer Lataste CSS Management System</h2>";
        $content .= "<p>";
        $content .= "<p>Only AEC-Foyer Lataste employees are authorized to use this Management System";
        $content .= "<p>Only the webmaster can and should make internal changes, otherwise he cannot be held accountable in case the system does not perform as expected";
        $content .= "<p>";
        $current_year = DATE('Y');
        $content .= "<p>Copyright {$current_year} - AEC Foyer Lataste - developed by <a href='ivanbragatto.byethost24.com'>IBweb</a>";
        $content .= "</div>";
        $content .= "</div>";
        $output['content']=$content;
        return $output;
    }
}