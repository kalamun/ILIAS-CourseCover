<?php
/**
 * Class ilCourseCoverPlugin
 * @author  Kalamun <rp@kalamun.net>
 * @version $Id$
 */

 class ilCourseCoverPlugin extends ilUserInterfaceHookPlugin
 {
    const CTYPE = "Services";
    const CNAME = "UIComponent";
    const SLOT_ID = "uihk";
    const PLUGIN_NAME = "CourseCover";

    protected static $instance = null;

    public function __construct(
        \ilDBInterface $db,
        \ilComponentRepositoryWrite $component_repository,
        string $id
    )
    {
        parent::__construct($db, $component_repository, $id);
    }

    public static function getInstance() : ilCourseCoverPlugin
    {
        global $DIC;

        if (self::$instance instanceof self) {
            return self::$instance;
        }

        $component_repository = $DIC['component.repository'];
        $component_factory = $DIC['component.factory'];

        $plugin_info = $component_repository->getComponentByTypeAndName(
            self::CTYPE,
            self::CNAME
        )->getPluginSlotById(self::SLOT_ID)->getPluginByName(self::PLUGIN_NAME);

        self::$instance = $component_factory->getPlugin($plugin_info->getId());

        return self::$instance;
    }

    public function getPluginName() : string
    {
        return self::PLUGIN_NAME;
    }

  public function set_cover() {
    if(method_exists($this->tpl,'loadStandardTemplate')) {
      $this->tpl->loadStandardTemplate();
    } else {
      $this->tpl->getStandardTemplate();
    }
    $this->initHeader();

    $this->initConfTabs();
    //$this->tabs->activateSubTab(self::TAB_CONFIG_SELF_PRINT);

    $form = $this->set_cover_form();

    $this->tpl->setContent($form->getHTML());
    if(method_exists($this->tpl, 'printToStdout'))
    {
      $this->tpl->printToStdout();
    } else {
      $this->tpl->show();
    }
  }

  
  public function set_cover_form() :ilPropertyFormGUI {

    $form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this));

		$form->setTitle("test");

		$enable = new ilCheckboxInputGUI("check test", 'enable_self_print');
		$enable->setChecked(true);
		$form->addItem($enable);

		$form->addCommandButton(self::CMD_SET_COVER, 'save');

		return $form;

  }

}
 