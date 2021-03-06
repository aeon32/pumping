<?php

require_once(PATH_BASE . '/framework/abstractcomponent.php');


class com_controllers extends AbstractComponent
{


    private $site = NULL;
    private $controllers_manager = NULL;

    public function __construct(CSite $site)
    {
        $this->site = $site;

        $this->controllers_manager = $site->getControllersManager();
        if ($site->getAction() == 'delete') {
            $this->delete();
        }
    }


    public function getTitle()
    {
        return "Управление продувкой";
    }

    /**
     * Функция возвращает ключевые слова (заголовок для страницы)
     * @return  string заголовок страницы
     */
    public function getKeyWords()
    {
        return "Управление продувкой";
    }

    /**
     * Функция возвращает description(заголовок для страницы)
     * @return  string заголовок страницы
     */
    public function getDescription()
    {
        return "Описание страницы";
    }


    public function getCSSFile()
    {
        return "new_main.css";
    }

    private function getReadableControllerStatus($controller)
    {
        $res = "Неизвестен";
        if ($controller->online &&  !is_null($controller->monitoring_info))
        {
            $res = $controller->monitoring_info->is_working ? "В работе" : "Простаивает";

        }
        return $res;

    }


    private function renderForm()
    {
        ?>
        <div class="content">
            <div class="header_div">
                <h1>Контроллеры</h1>
            </div>

            <div id="error_message" style="display:none">Ошибка обновления информации</div>
            <form name="admin_table" id="admin_table" method="post" action="">
                <input type="hidden" name="action" id="action" value="accept"/>
                <input type="hidden" id="basename" value="<?php print($this->site->getBaseName()); ?>"/>
                <table id="controllers_table" class="list_table" width="100%">
                    <tr>
                        <th class="td_small">#</th>
                        <th class="td_big">Название</th>
                        <th class="td_small">Статус</th>
                        <th class="td_small">Давление</th>
                        <th class="td_small">Номер зоны/шаг</th>

                    </tr>
                    <?php
                    $i = 1;
                    $controllers = $this->controllers_manager->getControllersList();
                    $polos = false;
                    foreach ($controllers as &$value) {
                        $class = $polos ? ' class="polos_tr" ' : NULL;
                        $status = $this->getReadableControllerStatus($value);
                        $actual_info = $value->online &&  !is_null($value->monitoring_info);
                        $pressure = $actual_info ? $value->monitoring_info->pressure : "";
                        $step = "";
                        if ($actual_info && $value->monitoring_info->is_working)
                            $step = $actual_info ? (string)$value->monitoring_info->current_valve."/".(string) $value->monitoring_info->current_step : "";


                        print("
	       <tr $class>
	        <td class=\"td_small\">$i</td>
			<td class=\"td_big\"><a href=\"controller/$value->id/\">" . htmlspecialchars($value->name) . "</a></td>
			<td class=\"td_small\">$status</td>
			<td class=\"td_small\">$pressure</td>
 			<td class=\"td_small\">$step</td>			
			
		</tr>");
                        $polos = !$polos;
                        $i++;
                    }
                    ?>
                </table>
            </form>
        </div>
        <?php
    }


    public function render()
    {
        $this->renderForm();
        ?>

        <?php
    }

    public function getAuxScripts()
    {
        return array('js/com_controllers.js');
        //return array('js/com_install.js');
    }

    /*
  * Функция удаляет множество схем
  *
  */
    public function delete()
    {
        $to_delete = array();
        $files_to_delete = array();
        foreach ($_POST as $key => $value) {
            $fl_array = array();
            if (preg_match("/selected([0-9]+)/", $key, $fl_array)) {
                $scheme_id = (int)$fl_array[1];
                if ($value == "on" && $scheme_id !== 0)
                    $to_delete[] = (int)($scheme_id);
            }
        }
        $to_delete[] = "-1";
        $to_delete = implode(',', $to_delete);
        $this->controllersManager->_delete_scheme(" controller_id in ($to_delete)");
    }

}


?>
