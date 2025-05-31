<?php
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Class ilCourseCoverUIHookGUI
 * @author            Kalamun <rp@kalamun.net>
 * @version $Id$
 * @ingroup ServicesUIComponent
 * @ilCtrl_isCalledBy ilCourseCoverUIHookGUI: ilUIPluginRouterGUI, ilAdministrationGUI, ilRepositoryGUI, ilObjCourseGUI
 */

class ilCourseCoverUIHookGUI extends ilUIHookPluginGUI {
  protected $user;
  protected $ctrl;
  protected $access;
  protected $tpl;
  
  protected $is_admin;
  protected $is_tutor;

  const CMD_SET_COVER = "set_cover";

  public function __construct()
  {
    global $DIC;
    $this->user = $DIC->user();
    $this->ctrl = $DIC->ctrl();
    $this->access = $DIC->access();
    global $tpl;
    $this->tpl = $tpl;

    $this->is_admin = false;
    $this->is_tutor = false;

    $global_roles_of_user = $DIC->rbac()->review()->assignedRoles($DIC->user()->getId());

		foreach ($DIC->rbac()->review()->getGlobalRoles() as $role){
      if (in_array($role, $global_roles_of_user)) {
        $role = new ilObjRole($role);
        if ($role->getTitle() == "Administrator") $this->is_admin = true;
        if ($role->getTitle() == "Tutor") $this->is_tutor = true;
      }
		}
  }

  /**
	 * Modify HTML output of GUI elements. Modifications modes are:
	 * - ilUIHookPluginGUI::KEEP (No modification)
	 * - ilUIHookPluginGUI::REPLACE (Replace default HTML with your HTML)
	 * - ilUIHookPluginGUI::APPEND (Append your HTML to the default HTML)
	 * - ilUIHookPluginGUI::PREPEND (Prepend your HTML to the default HTML)
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 *
	 * @return array array with entries "mode" => modification mode, "html" => your html
	 */
	function getHTML(string $a_comp, string $a_part, array $a_par = []) : array {
    global $tpl;  
    global $DIC;

    return ["mode" => ilUIHookPluginGUI::KEEP, "html" => ""];
  }


  /**
	 * Modify GUI objects, before they generate ouput
	 *
	 * @param string $a_comp component
	 * @param string $a_part string that identifies the part of the UI that is handled
	 * @param string $a_par array of parameters (depend on $a_comp and $a_part)
	 */
  function modifyGUI(string $a_comp, string $a_part, array $a_par = []) : void {
    if (empty($_GET['ref_id'])) return;
    
    if (empty($_SESSION['xcoursecover'])) {
      $_SESSION['xcoursecover'] = ['tabs' => [], 'sub_tabs' => []];
    }

    if ($a_part == "tabs") {
      $_SESSION['xcoursecover']['tabs'] = $a_par["tabs"]->target;
    }
    
    elseif ($a_part == "sub_tabs")
		{
      $has_access = $this->access->checkAccess("write", "", $_GET['ref_id']);
      $is_repository = strtolower($_GET['baseClass']) == "ilrepositorygui";
      $is_settings = strtolower($_GET['cmdClass']) == "ilobjcoursegui" && strtolower($_GET['cmd']) == "edit";
      $this->ctrl->setParameterByClass("ilCourseCoverGUI", "ref_id", $_GET['ref_id']);

      if ($has_access && $is_repository && $is_settings) {
        $a_id = "cover";
        $a_text = "Cover";
        $a_link = $this->ctrl->getLinkTargetByClass([
          ilUIPluginRouterGUI::class,
          ilCourseCoverGUI::class,
        ], self::CMD_SET_COVER);
        $a_frame = "";
        $a_par["tabs"]->addSubTab($a_id, $a_text, $a_link, $a_frame);
      }

      $_SESSION['xcoursecover']['sub_tabs'] = $a_par["tabs"]->sub_target;
		}
  }

}