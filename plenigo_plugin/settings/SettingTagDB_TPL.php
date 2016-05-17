<div class="container">
    <div class="panel panel-default">
        <div class="panel-body" style="width: 100%;text-align: center;">
            <input type="hidden" name="<?php echo self::PLENIGO_SETTINGS_NAME . '[' . static::SETTING_ID ?>]" id="<?php echo static::PREFIX_ID; ?>_db" value="<?php echo $currValue; ?>">
            <table style="margin-left: auto;margin-right: auto;">
                <tr>
                    <td colspan="2" style="text-align: right;"><?php echo __('Wordpress Tags', parent::PLENIGO_SETTINGS_GROUP); ?></td>
                    <td colspan="2">&nbsp;-&gt;&nbsp;</td>
                    <td style="text-align: left"><?php echo __('Plenigo Products', parent::PLENIGO_SETTINGS_GROUP); ?></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <select name="<?php echo static::PREFIX_ID; ?>_tags" id="<?php echo static::PREFIX_ID; ?>_tags">
                            <option value=""></option>
                            <?php
                            if ($tagList != "") {
                                $arrTags = explode(",", $tagList);
                                foreach ($arrTags as $tagItem) {
                                    echo '<option value="' . $tagItem . '">' . str_replace("{", " {", $tagItem) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td colspan="2">&nbsp;-&gt;&nbsp;</td>
                    <td>
                        <select name="<?php echo static::PREFIX_ID; ?>_prods" id="<?php echo static::PREFIX_ID; ?>_prods">
                            <option value=""></option>
                            <?php
                            if ($itemList != "") {
                                $arrItems = explode("|", $itemList);
                                foreach ($arrItems as $item) {
                                    $spliItem = explode(",", $item);
                                    echo '<option value="' . $spliItem[0] . '">' . $spliItem[1] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                    <td style="text-align: left;">
                        &nbsp;<button id="<?php echo static::PREFIX_ID; ?>_add_btn"><span class="ui-icon ui-icon-plus" style="display: inline-block"></span> <?php echo __("Add", self::PLENIGO_SETTINGS_GROUP); ?></button>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <select name="<?php echo static::PREFIX_ID; ?>_editor" id="<?php echo static::PREFIX_ID; ?>_editor" size="10" style="width: 100%;height: 300px;">
                        </select>
                    </td>
                    <td style="vertical-align: top;text-align: left;">
                        &nbsp;<button id="<?php echo static::PREFIX_ID; ?>_del_btn"><span class="ui-icon ui-icon-trash" style="display: inline-block"></span> <?php echo __("Remove", self::PLENIGO_SETTINGS_GROUP); ?></button><br/>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div><!-- /.container -->