<?php
namespace LPManager;

use LPManager\taxonomy\Client_Taxonomy;
use LPManager\taxonomy\Keyword_Taxonomy;
use LPManager\cpt\Landing_Page_CPT;
use LPManager\cpt\Landing_Page_CPT_Enhancements;
use LPManager\cpt\Rewrite_Rules;
use LPManager\admin\Admin_Dashboard;
use LPManager\admin\Plugin_Settings;
use LPManager\templates\Template_Manager;
use LPManager\templates\Template_Loader;
use LPManager\ajax\Reviews_Ajax;
use LPManager\assets\Assets_Manager;
use LPManager\core\Landing_Page_Validator;
use LPManager\Update_Checker;

final class Plugin {

    public static function init() {

        // Taxonomies and CPTs first — core data structures
        Client_Taxonomy::init();
        Keyword_Taxonomy::init();
        Rewrite_Rules::init();
        
        Landing_Page_CPT::init();
        Landing_Page_CPT_Enhancements::init();

        // Assets early if needed for admin screens (order can vary)
        Assets_Manager::init();
        Landing_Page_Validator::init();
        
        // Template management needs to initialize before anything loads template functions or fields
        Template_Manager::init();
        Template_Loader::init();
        Reviews_Ajax::init();
        
        // Tracking is loaded by the Pro plugin only.
        Admin_Dashboard::init();
        Plugin_Settings::init();
        Update_Checker::init();
    }

    public static function activate() {
        Landing_Page_CPT::register_cpt();
        Client_Taxonomy::register_taxonomy();
        Keyword_Taxonomy::register_taxonomy();
        flush_rewrite_rules();
    }
}
