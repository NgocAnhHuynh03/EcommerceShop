"use strict";

(function () {
  var $ = jQuery;
  elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {
    if (WlfmcPanelSettings.wlfmc_pro_widgets.length <= 0) return panel;
    var WlfmcWidgetsPromoHandler,
        WlfmcCategoryIndex,
        WlfmcElementsView = panel.elements.view,
        WlfmcCategoriesView = panel.categories.view,
        WlfmcWidgets = panel.elements.options.collection,
        WlfmcCategories = panel.categories.options.collection,
        WlfmcProCategory = [];

    _.each(WlfmcPanelSettings.wlfmc_pro_widgets, function (widget, index) {
      WlfmcWidgets.add({
        name: widget.key,
        title: widget.title,
        icon: widget.icon,
        categories: ["WLFMC_WishList-pro"],
        editable: false
      });
    });

    WlfmcWidgets.each(function (widget) {
      "WLFMC_WishList-pro" === widget.get("categories")[0] && WlfmcProCategory.push(widget);
    });
    WlfmcCategoryIndex = WlfmcCategories.findIndex({
      name: "WLFMC_WishList"
    });
    WlfmcCategoryIndex && WlfmcCategories.add({
      name: "WLFMC_WishList-pro",
      title: "MoreConvert Pro",
      defaultActive: !1,
      items: WlfmcProCategory
    }, {
      at: WlfmcCategoryIndex + 1
    });
    WlfmcWidgetsPromoHandler = {
      className: function className() {
        var className = 'elementor-element-wrapper';

        if (!this.isEditable()) {
          className += ' elementor-element--promotion';
        }

        return className;
      },
      isWlfmcWidget: function isWlfmcWidget() {
        return 0 === this.model.get("name").indexOf("wlfmc-premium-");
      },
      getElementObj: function getElementObj(key) {
        var widgetObj = WlfmcPanelSettings.wlfmc_pro_widgets.find(function (widget, index) {
          if (widget.key == key) return true;
        });
        return widgetObj;
      },
      onMouseDown: function onMouseDown() {
        if (!this.isWlfmcWidget()) return;
        void this.constructor.__super__.onMouseDown.call(this);
        var widgetObject = this.getElementObj(this.model.get("name")),
            actionURL = widgetObject.action_url;
        elementor.promotion.showDialog({
          title: sprintf(wp.i18n.__('%s', 'elementor'), this.model.get("title")),
          content: sprintf(wp.i18n.__('Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'elementor'), this.model.get("title")),
          top: "-7",
          targetElement: this.$el,
          actionButton: {
            url: actionURL,
            text: wp.i18n.__('Read More', 'elementor')
          }
        });
      }
    };
    panel.elements.view = WlfmcElementsView.extend({
      childView: WlfmcElementsView.prototype.childView.extend(WlfmcWidgetsPromoHandler)
    });
    panel.categories.view = WlfmcCategoriesView.extend({
      childView: WlfmcCategoriesView.prototype.childView.extend({
        childView: WlfmcCategoriesView.prototype.childView.prototype.childView.extend(WlfmcWidgetsPromoHandler)
      })
    });
    return panel;
  });
})(jQuery);