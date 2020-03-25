<?php

namespace BSW\OptimisationMegamenu\Block;

class Topmenu extends \Smartwave\Megamenu\Block\Topmenu
{

    protected $cache;
    protected $dataObjectFactory;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context,
                                \Magento\Catalog\Helper\Category $categoryHelper,
                                \Smartwave\Megamenu\Helper\Data $helper,
                                \Magento\Catalog\Model\Indexer\Category\Flat\State $categoryFlatState,
                                \Magento\Catalog\Model\CategoryFactory $categoryFactory,
                                \Magento\Theme\Block\Html\Topmenu $topMenu,
                                \Magento\Cms\Model\Template\FilterProvider $filterProvider,
                                \Magento\Cms\Model\BlockFactory $blockFactory,
                                \Magento\Framework\DataObjectFactory $dataObjectFactory,
                                \Magento\Framework\App\CacheInterface $cache)
    {
        parent::__construct($context, $categoryHelper, $helper, $categoryFlatState, $categoryFactory, $topMenu, $filterProvider, $blockFactory);

        $this->dataObjectFactory = $dataObjectFactory;
        $this->cache = $cache;
    }

    public function getMegamenuHtml()
    {
        if ($this->cache->load('megmencache') == true) {
            return $this->cache->load('megmencache');
        } else {

            $html = '';

            $categories = $this->getStoreCategories(true, false, true);

            $this->_megamenuConfig = $this->_helper->getConfig('sw_megamenu');

            $max_level = $this->_megamenuConfig['general']['max_level'];
            $html .= $this->getCustomBlockHtml('before');
            foreach ($categories as $category) {
                if (!$category->getIsActive()) {
                    continue;
                }

                $cat_model = $this->getCategoryModel($category->getId());

                $sw_menu_hide_item = $cat_model->getData('sw_menu_hide_item');

                if (!$sw_menu_hide_item) {
                    $children = $this->getActiveChildCategories($category);
                    $sw_menu_cat_label = $cat_model->getData('sw_menu_cat_label');
                    $sw_menu_icon_img = $cat_model->getData('sw_menu_icon_img');
                    $sw_menu_font_icon = $cat_model->getData('sw_menu_font_icon');
                    $sw_menu_cat_columns = $cat_model->getData('sw_menu_cat_columns');
                    $sw_menu_float_type = $cat_model->getData('sw_menu_float_type');

                    if (!$sw_menu_cat_columns) {
                        $sw_menu_cat_columns = 4;
                    }

                    $menu_type = $cat_model->getData('sw_menu_type');
                    if (!$menu_type)
                        $menu_type = $this->_megamenuConfig['general']['menu_type'];

                    $custom_style = '';
                    if ($menu_type == "staticwidth")
                        $custom_style = ' style="width: 500px;"';

                    $sw_menu_static_width = $cat_model->getData('sw_menu_static_width');
                    if ($menu_type == "staticwidth" && $sw_menu_static_width)
                        $custom_style = ' style="width: ' . $sw_menu_static_width . ';"';

                    $item_class = 'level0 ';
                    $item_class .= $menu_type . ' ';

                    $menu_top_content = $cat_model->getData('sw_menu_block_top_content');
                    $menu_left_content = $cat_model->getData('sw_menu_block_left_content');
                    $menu_left_width = $cat_model->getData('sw_menu_block_left_width');
                    if (!$menu_left_content || !$menu_left_width)
                        $menu_left_width = 0;
                    $menu_right_content = $cat_model->getData('sw_menu_block_right_content');
                    $menu_right_width = $cat_model->getData('sw_menu_block_right_width');
                    if (!$menu_right_content || !$menu_right_width)
                        $menu_right_width = 0;
                    $menu_bottom_content = $cat_model->getData('sw_menu_block_bottom_content');
                    if ($sw_menu_float_type)
                        $sw_menu_float_type = 'fl-' . $sw_menu_float_type . ' ';
                    if (count($children) > 0 || (($menu_type == "fullwidth" || $menu_type == "staticwidth") && ($menu_top_content || $menu_left_content || $menu_right_content || $menu_bottom_content)))
                        $item_class .= 'parent ';
                    $html .= '<li class="ui-menu-item ' . $item_class . $sw_menu_float_type . '">';
                    if (count($children) > 0) {
                        $html .= '<div class="open-children-toggle"></div>';
                    }
                    $html .= '<a href="' . $this->_categoryHelper->getCategoryUrl($category) . '" class="level-top">';
                    if ($sw_menu_icon_img)
                        $html .= '<img class="menu-thumb-icon" src="' . $this->_helper->getBaseUrl() . 'catalog/category/' . $sw_menu_icon_img . '" alt="' . $category->getName() . '"/>';
                    elseif ($sw_menu_font_icon)
                        $html .= '<em class="menu-thumb-icon ' . $sw_menu_font_icon . '"></em>';
                    $html .= '<span>' . $category->getName() . '</span>';
                    if ($sw_menu_cat_label)
                        $html .= '<span class="cat-label cat-label-' . $sw_menu_cat_label . '">' . $this->_megamenuConfig['cat_labels'][$sw_menu_cat_label] . '</span>';
                    $html .= '</a>';
                    if (count($children) > 0 || (($menu_type == "fullwidth" || $menu_type == "staticwidth") && ($menu_top_content || $menu_left_content || $menu_right_content || $menu_bottom_content))) {
                        $html .= '<div class="level0 submenu"' . $custom_style . '>';
                        if (($menu_type == "fullwidth" || $menu_type == "staticwidth") && $menu_top_content) {
                            $html .= '<div class="menu-top-block">' . $this->getBlockContent($menu_top_content) . '</div>';
                        }
                        if (count($children) > 0 || (($menu_type == "fullwidth" || $menu_type == "staticwidth") && ($menu_left_content || $menu_right_content))) {
                            $html .= '<div class="row">';
                            if (($menu_type == "fullwidth" || $menu_type == "staticwidth") && $menu_left_content && $menu_left_width > 0) {
                                $html .= '<div class="menu-left-block col-sm-' . $menu_left_width . '">' . $this->getBlockContent($menu_left_content) . '</div>';
                            }
                            $html .= $this->getSubmenuItemsHtml($children, 1, $max_level, 12 - $menu_left_width - $menu_right_width, $menu_type, $sw_menu_cat_columns);
                            if (($menu_type == "fullwidth" || $menu_type == "staticwidth") && $menu_right_content && $menu_right_width > 0) {
                                $html .= '<div class="menu-right-block col-sm-' . $menu_right_width . '">' . $this->getBlockContent($menu_right_content) . '</div>';
                            }
                            $html .= '</div>';
                        }
                        if (($menu_type == "fullwidth" || $menu_type == "staticwidth") && $menu_bottom_content) {
                            $html .= '<div class="menu-bottom-block">' . $this->getBlockContent($menu_bottom_content) . '</div>';
                        }
                        $html .= '</div>';
                    }
                    $html .= '</li>';
                }
            }
            $html .= $this->getCustomBlockHtml('after');

            $this->cache->save($html, 'megmencache',[] ,86400);
            return $html;
        }
    }
}
