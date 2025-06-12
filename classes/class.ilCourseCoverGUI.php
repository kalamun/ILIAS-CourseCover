<?php
/**
 * Class ilCourseCoverGUI
 *
 * @ilCtrl_isCalledBy ilCourseCoverGUI: ilUIPluginRouterGUI, ilCourseCoverUIHookGUI
 */

 class ilCourseCoverGUI {

  const CMD_SAVE = 'save';
	const DOWNLOADFILE = 'downloadFile';
  const CMD_SET_COVER = "set_cover";

	public $tpl;
	public $ctrl;
	public $tabs;
	public $lng;
	public $db;
	public $db_table_name = "uichk_xcoursecover";

	function __construct() {
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->tpl = $DIC->ui()->mainTemplate();
		$this->tabs = $DIC->tabs();
		$this->lng = $DIC->language();
		$this->db = $DIC->database();
	}

	function executeCommand() {
		$cmd = $this->ctrl->getCmd();
		$nextClass = $this->ctrl->getNextClass();

		switch ($nextClass) {
			case strtolower(ilCourseCoverResultGUI::class):
				$ilCourseCoverTableGUI = new ilCourseCoverResultGUI();
				$this->ctrl->forwardCommand($ilCourseCoverTableGUI);
				break;
			default:
				switch ($cmd) {
					case self::CMD_SET_COVER:
					case self::CMD_SAVE:
					case self::DOWNLOADFILE:
						$this->{$cmd}();
						break;
					default:
						throw new ilException("Unknown command: '$cmd'");
						break;
				}
		}
	}


  public function set_cover() {
    if(method_exists($this->tpl,'loadStandardTemplate')) {
      $this->tpl->loadStandardTemplate();
    } else {
      $this->tpl->getStandardTemplate();
    }
    $this->initHeader();

    $this->initConfTabs();
    $this->tabs->activateSubTab("cover");

    $form = $this->initForm();

    $this->tpl->setContent($form->getHTML());
    if(method_exists($this->tpl, 'printToStdout'))
    {
      $this->tpl->printToStdout();
    } else {
      $this->tpl->show();
    }
  }

  
  public function initForm() :ilPropertyFormGUI {
    $form = new ilPropertyFormGUI();

		$form->setFormAction($this->ctrl->getFormAction($this));

		$form->setTitle("Cover");
		$props = $this->getCoverIdsByObjectId($_REQUEST['ref_id']);
		if (empty($props)) {
			$props = [
				"ref_id" => false,
				"cover_logo_id" => false,
				"cover_square_id" => false,
				"cover_banner_id" => false,
				"cover_banner2_id" => false,
				"cover_banner3_id" => false,
			];
		}

		// ref_id
		$refid_field = new ilHiddenInputGUI('ref_id');
		$refid_field->setValue($_REQUEST['ref_id']);
		$form->addItem($refid_field);

		// logo
		$logo_field = new ilImageFileInputGUI($this->lng->txt("logo"), 'cover_logo_id');
		$logo_field->setAllowDeletion(false);
		$logo_field->setRequired(false);
		$form->addItem($logo_field);
		$image_url = $this->getCoverUrlById($props['cover_logo_id']);
		if (!empty($image_url)) $logo_field->setImage($image_url);

		// square thumbnail
		$square = new ilImageFileInputGUI($this->lng->txt("square"), 'cover_square_id');
		$square->setAllowDeletion(false);
		$square->setRequired(false);
		$form->addItem($square);
		$image_url = $this->getCoverUrlById($props['cover_square_id']);
		if (!empty($image_url)) $square->setImage($image_url);

		// banner
		$banner = new ilImageFileInputGUI($this->lng->txt("banner"), 'cover_banner_id');
		$banner->setAllowDeletion(false);
		$banner->setRequired(false);
		$form->addItem($banner);
		$image_url = $this->getCoverUrlById($props['cover_banner_id']);
		if (!empty($image_url)) $banner->setImage($image_url);

		$banner2 = new ilImageFileInputGUI($this->lng->txt("banner-2"), 'cover_banner2_id');
		$banner2->setAllowDeletion(false);
		$banner2->setRequired(false);
		$form->addItem($banner2);
		$image_url = $this->getCoverUrlById($props['cover_banner2_id']);
		if (!empty($image_url)) $banner2->setImage($image_url);

		$banner3 = new ilImageFileInputGUI($this->lng->txt("banner-3"), 'cover_banner3_id');
		$banner3->setAllowDeletion(false);
		$banner3->setRequired(false);
		$form->addItem($banner3);
		$image_url = $this->getCoverUrlById($props['cover_banner3_id']);
		if (!empty($image_url)) $banner3->setImage($image_url);

		$form->addCommandButton(self::CMD_SAVE, 'save');
		return $form;
  }

	private function getCoverIdsByObjectId($obj_id) {
		$db_query = $this->db->query("SELECT * FROM " . $this->db_table_name . " WHERE `ref_id`='" . intval($obj_id) . "'");
		$results = $this->db->fetchAssoc($db_query);
		return $results;
	}

	private function getCoverUrlById($image_id) {
		$image_url = false;
		if (empty($image_id)) return $image_url;

		$fileObj = new ilObjFile($image_id, false);
		if (!empty($fileObj)) {
			$_SESSION[__CLASS__]['allowedFiles'][$fileObj->getId()] = true;
			$this->ctrl->setParameter($this, 'id', $fileObj->getId());
			$image_url = $this->ctrl->getLinkTargetByClass(['ilUIPluginRouterGUI', 'ilCourseCoverGUI'], 'downloadFile');
		}
		return $image_url;
	}

	public function getCoverURL($ref_id, $type) {
		$ids = $this->getCoverIdsByObjectId($ref_id);
		if (empty($ids)) return '';

		$key = 'cover_' . $type . '_id';
		$image_id = !empty($ids[$key]) ? $ids[$key] : '';
		if (empty($image_id)) return '';

		return $this->getCoverUrlById($image_id);
	}


	/**
	 *
	 */
	function initHeader() {
		$obj = ilObjectFactory::getInstanceByRefId(intval($_REQUEST['ref_id']), false);
		$this->tpl->setTitle($obj->getTitle());
		$this->tpl->setDescription($obj->getDescription());
		$this->ctrl->saveParameterByClass(ilRepositoryGUI::class, 'ref_id');
 	}


	/**
	 *
	 */
	protected function initConfTabs() {
		if (!empty($_SESSION['xcoursecover']['tabs'])) {
			foreach ($_SESSION['xcoursecover']['tabs'] as $tab) {
				$a_id = $tab['id'];
        $a_text = $tab['text'];
        $a_link = $tab['link'];
        $a_frame = $tab['frame'];
        $this->tabs->addTab($a_id, $a_text, $a_link, $a_frame);
			}
		}
		if (!empty($_SESSION['xcoursecover']['sub_tabs'])) {
			foreach ($_SESSION['xcoursecover']['sub_tabs'] as $tab) {
				$a_id = $tab['id'];
        $a_text = $this->lng->txt($tab['text']);
        $a_link = $tab['link'];
        $a_frame = $tab['frame'];
        $this->tabs->addSubTab($a_id, $a_text, $a_link, $a_frame);
			}
		}
	}

	public function save() : void
	{
		$form = $this->initForm(false);
		if ($this->saveForm($form, false)) {
			$this->tpl->setOnScreenMessage("success", $this->lng->txt("msg_obj_modified"), true);
		}
		$this->set_cover();
	}

	private function saveForm(ilPropertyFormGUI $form, bool $a_create) : bool {
		$ref_id = $_REQUEST['ref_id'];
		if (empty($ref_id)) return false;

		$fields = [
			"cover_logo_id",
			"cover_square_id",
			"cover_banner_id",
			"cover_banner2_id",
			"cover_banner3_id",
		];
		$success = [];

		$props = $this->getCoverIdsByObjectId($ref_id);

		foreach($fields as $key) {
			if (!empty($_REQUEST[$key . "_delete"]) && !empty($props[$key])) {
					$fileObj = new ilObjFile((int) $props[$key], false);
					$fileObj->setType("file");
					$fileObj->doDelete();
					$success[$key] = "NULL";

			} elseif (!empty($_FILES[$key]["name"])) {
					$old_file_id = empty($props[$key]) ? null : $props[$key];
					
					$fileObj = new ilObjFile((int) $old_file_id, false);
					$fileObj->setType("file");
					$fileObj->setTitle($_FILES[$key]["name"]);
					$fileObj->setDescription("");
					$fileObj->setFileName($_FILES[$key]["name"]);
					$fileObj->setMode("filelist");
					if (empty($old_file_id)) {
							$fileObj->create();
					} else {
							$fileObj->update();
					}

					// upload file to filesystem
					if ($_FILES[$key]["tmp_name"] !== "") {
							$fileObj->getUploadFile(
									$_FILES[$key]["tmp_name"],
									$_FILES[$key]["name"]
							);
					}

					$success[$key] = $fileObj->getId();
			}
		}

		if (count($success) == 0) return false;
		
		$success['ref_id'] = intval($ref_id);
		
		$query = "INSERT INTO " . $this->db_table_name . " ". $this->successToInsert($success) ." ON DUPLICATE KEY UPDATE ". $this->successToUpdate($success) .";";
		$result = $this->db->query($query);
		return true;
	}

	private function successToInsert($success) {
		$output = "(";
		foreach ($success as $key => $value) {
			$output .= $key . ",";
		}
		$output = rtrim($output, ",");
		$output .= ") VALUES(";
		foreach ($success as $key => $value) {
			$output .= $value === "NULL" ? "NULL," : "'" . intval($value) . "',";
		}
		$output = rtrim($output, ",") . ")";
		return $output;
	}
	
	private function successToUpdate($success) {
		$output = "";
		foreach ($success as $key => $value) {
			$output .= "`". $key . "`=" . ($value === "NULL" ? "NULL," : "'" . intval($value)) . "',";
		}
		$output = rtrim($output, ",");
		return $output;
	}

	/**
	 * download file of file lists
	 */
	public function downloadFile() : void
	{
			$file_id = (int) $_GET['id'];
			if ($_SESSION[__CLASS__]['allowedFiles'][$file_id]) {
					$fileObj = new ilObjFile($file_id, false);
					$fileObj->sendFile();
			} else {
					throw new ilException('not allowed');
			}
	}

}