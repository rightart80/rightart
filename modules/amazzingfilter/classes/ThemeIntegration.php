<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class ThemeIntegration
{
    public function getIdentifier()
    {
        if (Module::isEnabled('is_themecore')) {
            $identifier = 'falcon';
        } else {
            $identifier = _THEME_NAME_;
            if (defined('_PARENT_THEME_NAME_') && _PARENT_THEME_NAME_) {
                $identifier = _PARENT_THEME_NAME_;
            }
        }
        if ($this->isRetro()) {
            $identifier .= '-16';
        }

        return $identifier;
    }

    public function getSnippet($type, $params = [])
    {
        $snippet = [];
        switch ($type) {
            case 'btn':
                $snippet = [
                    'tpl' => $this->getThemeTpl('templates/catalog/_partials/products-top.tpl'),
                    'code' => '{if !empty($af_btn) && $af_btn.external}{include file=$af_btn.tpl}{/if}' . PHP_EOL,
                    'insert_marker' => $params['external'] ? '{if !empty($listing.rendered_facets)' : false,
                ];
                if ($this->isRetro()) {
                    $snippet['tpl'] = $this->getThemeTpl('product-sort.tpl');
                    $snippet['insert_marker'] = $params['external'] ? '{if isset($orderby)' : false;
                }
                break;
        }

        return $snippet;
    }

    public function getThemeTpl($path)
    {
        foreach (['_PS_THEME_DIR_', '_PS_PARENT_THEME_DIR_'] as $const_dir_name) {
            if (defined($const_dir_name) && $dir = constant($const_dir_name)) {
                if (file_exists($dir . $path)) {
                    return $dir . $path;
                }
            }
        }

        return false;
    }

    public function processBtn($external)
    {
        if ($snippet = $this->getSnippet('btn', ['external' => $external])) {
            return $this->updateTpl($snippet['tpl'], $snippet['code'], $snippet['insert_marker']);
        }
    }

    public function updateTpl($tpl_path, $code, $insert_marker = null)
    {
        if ($result = $tpl_path && file_exists($tpl_path)) {
            $orig_tpl_content = $upd_tpl_content = Tools::file_get_contents($tpl_path);
            $is_inserted = strpos($orig_tpl_content, $code) !== false;
            if (!$insert_marker && $is_inserted) { // remove code
                $indent = $this->getIndent($code, $orig_tpl_content);
                $upd_tpl_content = str_replace($indent . $code, '', $orig_tpl_content);
            } elseif ($insert_marker && !$is_inserted) { // insert code
                if (strpos($orig_tpl_content, $insert_marker) === false) {
                    $result = false;
                } else {
                    $indent = $this->getIndent($insert_marker, $orig_tpl_content);
                    $upd_tpl_content = str_replace(
                        $indent . $insert_marker,
                        $indent . $code . $indent . $insert_marker,
                        $orig_tpl_content
                    );
                }
            }
            if ($upd_tpl_content !== $orig_tpl_content) {
                $tmp_backup = $tpl_path . '.' . uniqid() . '.bak';
                if ($result &= Tools::copy($tpl_path, $tmp_backup)) {
                    if (!$result = (bool) file_put_contents($tpl_path, $upd_tpl_content)) {
                        if (Tools::file_get_contents($tpl_path) != $orig_tpl_content) {
                            Tools::copy($tmp_backup, $tpl_path);
                        }
                    }
                    unlink($tmp_backup);
                }
            }
        }

        return $result;
    }

    public function getIndent($fragment, $content)
    {
        preg_match('/^([ \t]*)' . preg_quote($fragment, '/') . '/m', $content, $m);

        return !empty($m[1]) ? $m[1] : '';
    }

    public function isRetro()
    {
        return Tools::substr(_PS_VERSION_, 0, 3) === '1.6';
    }
}
