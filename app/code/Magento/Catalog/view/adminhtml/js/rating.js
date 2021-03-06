/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

(function($) {
    'use strict';
    $.widget("marketing.ratingControl", {
        options: {
            colorFilled: '#333',
            colorUnfilled: '#CCCCCC',
            colorHover: '#f30'
        },

        _create: function() {
            this._labels = this.element.find('label');
            this._bind();
        },

        _bind: function() {
            this._labels.on({
                click: $.proxy(function(e) {
                    var elem = $(e.currentTarget);
                    $('#' + elem.attr('for')).attr('checked', 'checked');
                    this._updateRating();
                }, this),

                hover: $.proxy(function(e) {
                    this._updateHover($(e.currentTarget), this.options.colorHover);
                }, this),

                mouseleave: $.proxy(function(e) {
                    this._updateHover($(e.currentTarget), this.options.colorUnfilled);
                }, this)
            });

            this._updateRating();
        },

        _updateHover: function(elem, color) {
            elem.nextAll('label').andSelf().filter(function() {
                return !$(this).data('checked');
            }).css('color', color);
        },

        _updateRating: function() {
            var checkedInputs = this.element.find('input[type="radio"]:checked');
            checkedInputs.nextAll('label').andSelf().css('color', this.options.colorFilled).data('checked', true);
            checkedInputs.prevAll('label').css('color', this.options.colorUnfilled).data('checked', false);
        }
    });
})(jQuery);
