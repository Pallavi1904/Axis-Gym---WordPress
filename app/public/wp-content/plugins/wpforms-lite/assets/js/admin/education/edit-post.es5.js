(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

function _slicedToArray(r, e) { return _arrayWithHoles(r) || _iterableToArrayLimit(r, e) || _unsupportedIterableToArray(r, e) || _nonIterableRest(); }
function _nonIterableRest() { throw new TypeError("Invalid attempt to destructure non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function _iterableToArrayLimit(r, l) { var t = null == r ? null : "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (null != t) { var e, n, i, u, a = [], f = !0, o = !1; try { if (i = (t = t.call(r)).next, 0 === l) { if (Object(t) !== t) return; f = !1; } else for (; !(f = (e = i.call(t)).done) && (a.push(e.value), a.length !== l); f = !0); } catch (r) { o = !0, n = r; } finally { try { if (!f && null != t.return && (u = t.return(), Object(u) !== u)) return; } finally { if (o) throw n; } } return a; } }
function _arrayWithHoles(r) { if (Array.isArray(r)) return r; }
/* global wpforms_edit_post_education */

// noinspection ES6ConvertVarToLetConst
/**
 * WPForms Edit Post Education function.
 *
 * @since 1.8.1
 */

// eslint-disable-next-line no-var, no-unused-vars
var WPFormsEditPostEducation = window.WPFormsEditPostEducation || function (document, window, $) {
  // The identifiers for the Redux stores.
  var coreEditSite = 'core/edit-site',
    coreEditor = 'core/editor',
    coreBlockEditor = 'core/block-editor',
    coreNotices = 'core/notices',
    // Heading block name.
    coreHeading = 'core/heading';

  /**
   * Public functions and properties.
   *
   * @since 1.8.1
   *
   * @type {Object}
   */
  var app = {
    /**
     * Determine if the notice was shown before.
     *
     * @since 1.8.1
     */
    isNoticeVisible: false,
    /**
     * Identifier for the plugin and notice.
     *
     * @since 1.9.5
     */
    pluginId: 'wpforms-edit-post-product-education-guide',
    /**
     * Start the engine.
     *
     * @since 1.8.1
     */
    init: function init() {
      $(window).on('load', function () {
        // In the case of jQuery 3.+, we need to wait for a ready event first.
        if (typeof $.ready.then === 'function') {
          $.ready.then(app.load);
        } else {
          app.load();
        }
      });
    },
    /**
     * Page load.
     *
     * @since 1.8.1
     * @since 1.9.5 Added compatibility for the Site Editor.
     */
    load: function load() {
      if (!app.isGutenbergEditor()) {
        app.maybeShowClassicNotice();
        app.bindClassicEvents();
        return;
      }
      app.maybeShowGutenbergNotice();

      // "core/edit-site" store available only in the Site Editor.
      if (!!wp.data.select(coreEditSite)) {
        app.subscribeForSiteEditor();
        return;
      }
      app.subscribeForBlockEditor();
    },
    /**
     * This method listens for changes in the WordPress data store and performs the following actions:
     * - Monitors the editor title and focus mode to detect changes.
     * - Dismisses a custom notice if the focus mode is disabled and the notice is visible.
     * - Shows a custom Gutenberg notice if the title or focus mode changes.
     *
     * @since 1.9.5
     */
    subscribeForSiteEditor: function subscribeForSiteEditor() {
      // Store the initial editor title and focus mode state.
      var prevTitle = app.getEditorTitle();
      var prevFocusMode = null;
      var _wp$data = wp.data,
        subscribe = _wp$data.subscribe,
        select = _wp$data.select,
        dispatch = _wp$data.dispatch;

      // Listen for changes in the WordPress data store.
      subscribe(function () {
        // Fetch the current editor mode setting.
        // If true - Site Editor canvas is opened, and you can edit something.
        // If false - you should see the sidebar with navigation and preview
        // with selected template or page.
        var _select$getEditorSett = select(coreEditor).getEditorSettings(),
          focusMode = _select$getEditorSett.focusMode;

        // If focus mode is disabled and a notice is visible, remove the notice.
        // This is essential because user can switch pages / templates
        // without a page-reload.
        if (!focusMode && app.isNoticeVisible) {
          app.isNoticeVisible = false;
          prevFocusMode = focusMode;
          dispatch(coreNotices).removeNotice(app.pluginId);
        }
        var title = app.getEditorTitle();

        // If neither the title nor the focus mode has changed, do nothing.
        if (prevTitle === title && prevFocusMode === focusMode) {
          return;
        }

        // Update the previous title and focus mode values for the next subscription cycle.
        prevTitle = title;
        prevFocusMode = focusMode;

        // Show a custom Gutenberg notice if conditions are met.
        app.maybeShowGutenbergNotice();
      });
    },
    /**
     * Subscribes to changes in the WordPress block editor and monitors the editor's title.
     * When the title changes, it triggers a process to potentially display a Gutenberg notice.
     * The subscription is automatically stopped if the notice becomes visible.
     *
     * @since 1.9.5
     */
    subscribeForBlockEditor: function subscribeForBlockEditor() {
      var prevTitle = app.getEditorTitle();
      var subscribe = wp.data.subscribe;

      // Subscribe to WordPress data changes.
      var unsubscribe = subscribe(function () {
        var title = app.getEditorTitle();

        // Check if the title has changed since the previous value.
        if (prevTitle === title) {
          return;
        }

        // Update the previous title to the current title.
        prevTitle = title;
        app.maybeShowGutenbergNotice();

        // If the notice is visible, stop the WordPress data subscription.
        if (app.isNoticeVisible) {
          unsubscribe();
        }
      });
    },
    /**
     * Retrieves the title of the post currently being edited. If in the Site Editor,
     * it attempts to fetch the title from the topmost heading block. Otherwise, it
     * retrieves the title attribute of the edited post.
     *
     * @since 1.9.5
     *
     * @return {string} The post title or an empty string if no title is found.
     */
    getEditorTitle: function getEditorTitle() {
      var select = wp.data.select;

      // Retrieve the title for Post Editor.
      if (!select(coreEditSite)) {
        return select(coreEditor).getEditedPostAttribute('title');
      }
      if (app.isEditPostFSE()) {
        return app.getPostTitle();
      }
      return app.getTopmostHeadingTitle();
    },
    /**
     * Retrieves the content of the first heading block.
     *
     * @since 1.9.5
     *
     * @return {string} The topmost heading content or null if not found.
     */
    getTopmostHeadingTitle: function getTopmostHeadingTitle() {
      var _headingBlock$attribu, _headingBlock$attribu2;
      var select = wp.data.select;
      var headings = select(coreBlockEditor).getBlocksByName(coreHeading);
      if (!headings.length) {
        return '';
      }
      var headingBlock = select(coreBlockEditor).getBlock(headings[0]);
      return (_headingBlock$attribu = headingBlock === null || headingBlock === void 0 || (_headingBlock$attribu2 = headingBlock.attributes) === null || _headingBlock$attribu2 === void 0 || (_headingBlock$attribu2 = _headingBlock$attribu2.content) === null || _headingBlock$attribu2 === void 0 ? void 0 : _headingBlock$attribu2.text) !== null && _headingBlock$attribu !== void 0 ? _headingBlock$attribu : '';
    },
    /**
     * Determines if the current editing context is for a post type in the Full Site Editor (FSE).
     *
     * @since 1.9.5
     *
     * @return {boolean} True if the current context represents a post type in the FSE, otherwise false.
     */
    isEditPostFSE: function isEditPostFSE() {
      var select = wp.data.select;
      var _select$getPage = select(coreEditSite).getPage(),
        context = _select$getPage.context;
      return !!(context !== null && context !== void 0 && context.postType);
    },
    /**
     * Retrieves the title of a post based on its type and ID from the current editing context.
     *
     * @since 1.9.5
     *
     * @return {string} The title of the post.
     */
    getPostTitle: function getPostTitle() {
      var select = wp.data.select;
      var _select$getPage2 = select(coreEditSite).getPage(),
        context = _select$getPage2.context;

      // Use `getEditedEntityRecord` instead of `getEntityRecord`
      // to fetch the live, updated data for the post being edited.
      var _ref = select('core').getEditedEntityRecord('postType', context.postType, context.postId) || {},
        _ref$title = _ref.title,
        title = _ref$title === void 0 ? '' : _ref$title;
      return title;
    },
    /**
     * Bind events for Classic Editor.
     *
     * @since 1.8.1
     */
    bindClassicEvents: function bindClassicEvents() {
      var $document = $(document);
      if (!app.isNoticeVisible) {
        $document.on('input', '#title', _.debounce(app.maybeShowClassicNotice, 1000));
      }
      $document.on('click', '.wpforms-edit-post-education-notice-close', app.closeNotice);
    },
    /**
     * Determine if the editor is Gutenberg.
     *
     * @since 1.8.1
     *
     * @return {boolean} True if the editor is Gutenberg.
     */
    isGutenbergEditor: function isGutenbergEditor() {
      return typeof wp !== 'undefined' && typeof wp.blocks !== 'undefined';
    },
    /**
     * Create a notice for Gutenberg.
     *
     * @since 1.8.1
     */
    showGutenbergNotice: function showGutenbergNotice() {
      wp.data.dispatch(coreNotices).createInfoNotice(wpforms_edit_post_education.gutenberg_notice.template, app.getGutenbergNoticeSettings());

      // The notice component doesn't have a way to add HTML id or class to the notice.
      // Also, the notice became visible with a delay on old Gutenberg versions.
      var hasNotice = setInterval(function () {
        var noticeBody = $('.wpforms-edit-post-education-notice-body');
        if (!noticeBody.length) {
          return;
        }
        var $notice = noticeBody.closest('.components-notice');
        $notice.addClass('wpforms-edit-post-education-notice');
        $notice.find('.is-secondary, .is-link').removeClass('is-secondary').removeClass('is-link').addClass('is-primary');

        // We can't use onDismiss callback as it was introduced in WordPress 6.0 only.
        var dismissButton = $notice.find('.components-notice__dismiss');
        if (dismissButton) {
          dismissButton.on('click', function () {
            app.updateUserMeta();
          });
        }
        clearInterval(hasNotice);
      }, 100);
    },
    /**
     * Get settings for the Gutenberg notice.
     *
     * @since 1.8.1
     *
     * @return {Object} Notice settings.
     */
    getGutenbergNoticeSettings: function getGutenbergNoticeSettings() {
      var noticeSettings = {
        id: app.pluginId,
        isDismissible: true,
        HTML: true,
        __unstableHTML: true,
        actions: [{
          className: 'wpforms-edit-post-education-notice-guide-button',
          variant: 'primary',
          label: wpforms_edit_post_education.gutenberg_notice.button
        }]
      };
      if (!wpforms_edit_post_education.gutenberg_guide) {
        noticeSettings.actions[0].url = wpforms_edit_post_education.gutenberg_notice.url;
        return noticeSettings;
      }
      var Guide = wp.components.Guide,
        useState = wp.element.useState,
        _wp$plugins = wp.plugins,
        registerPlugin = _wp$plugins.registerPlugin,
        unregisterPlugin = _wp$plugins.unregisterPlugin;
      var GutenbergTutorial = function GutenbergTutorial() {
        var _useState = useState(true),
          _useState2 = _slicedToArray(_useState, 2),
          isOpen = _useState2[0],
          setIsOpen = _useState2[1];
        if (!isOpen) {
          return null;
        }
        return (
          /*#__PURE__*/
          // eslint-disable-next-line react/react-in-jsx-scope
          React.createElement(Guide, {
            className: "edit-post-welcome-guide",
            onFinish: function onFinish() {
              unregisterPlugin(app.pluginId);
              setIsOpen(false);
            },
            pages: app.getGuidePages()
          })
        );
      };
      noticeSettings.actions[0].onClick = function () {
        return registerPlugin(app.pluginId, {
          render: GutenbergTutorial
        });
      };
      return noticeSettings;
    },
    /**
     * Get Guide pages in proper format.
     *
     * @since 1.8.1
     *
     * @return {Array} Guide Pages.
     */
    getGuidePages: function getGuidePages() {
      var pages = [];
      wpforms_edit_post_education.gutenberg_guide.forEach(function (page) {
        pages.push({
          /* eslint-disable react/react-in-jsx-scope */
          content: /*#__PURE__*/React.createElement(React.Fragment, null, /*#__PURE__*/React.createElement("h1", {
            className: "edit-post-welcome-guide__heading"
          }, page.title), /*#__PURE__*/React.createElement("p", {
            className: "edit-post-welcome-guide__text"
          }, page.content)),
          image: /*#__PURE__*/React.createElement("img", {
            className: "edit-post-welcome-guide__image",
            src: page.image,
            alt: page.title
          })
          /* eslint-enable react/react-in-jsx-scope */
        });
      });
      return pages;
    },
    /**
     * Show notice if the page title matches some keywords for Classic Editor.
     *
     * @since 1.8.1
     */
    maybeShowClassicNotice: function maybeShowClassicNotice() {
      if (app.isNoticeVisible) {
        return;
      }
      if (app.isTitleMatchKeywords($('#title').val())) {
        app.isNoticeVisible = true;
        $('.wpforms-edit-post-education-notice').removeClass('wpforms-hidden');
      }
    },
    /**
     * Show notice if the page title matches some keywords for Gutenberg Editor.
     *
     * @since 1.8.1
     */
    maybeShowGutenbergNotice: function maybeShowGutenbergNotice() {
      if (app.isNoticeVisible) {
        return;
      }
      var title = app.getEditorTitle();
      if (app.isTitleMatchKeywords(title)) {
        app.isNoticeVisible = true;
        app.showGutenbergNotice();
      }
    },
    /**
     * Determine if the title matches keywords.
     *
     * @since 1.8.1
     *
     * @param {string} titleValue Page title value.
     *
     * @return {boolean} True if the title matches some keywords.
     */
    isTitleMatchKeywords: function isTitleMatchKeywords(titleValue) {
      var expectedTitleRegex = new RegExp(/\b(contact|form)\b/i);
      return expectedTitleRegex.test(titleValue);
    },
    /**
     * Close a notice.
     *
     * @since 1.8.1
     */
    closeNotice: function closeNotice() {
      $(this).closest('.wpforms-edit-post-education-notice').remove();
      app.updateUserMeta();
    },
    /**
     * Update user meta and don't show the notice next time.
     *
     * @since 1.8.1
     */
    updateUserMeta: function updateUserMeta() {
      $.post(wpforms_edit_post_education.ajax_url, {
        action: 'wpforms_education_dismiss',
        nonce: wpforms_edit_post_education.education_nonce,
        section: 'edit-post-notice'
      });
    }
  };
  return app;
}(document, window, jQuery);
WPFormsEditPostEducation.init();
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJXUEZvcm1zRWRpdFBvc3RFZHVjYXRpb24iLCJ3aW5kb3ciLCJkb2N1bWVudCIsIiQiLCJjb3JlRWRpdFNpdGUiLCJjb3JlRWRpdG9yIiwiY29yZUJsb2NrRWRpdG9yIiwiY29yZU5vdGljZXMiLCJjb3JlSGVhZGluZyIsImFwcCIsImlzTm90aWNlVmlzaWJsZSIsInBsdWdpbklkIiwiaW5pdCIsIm9uIiwicmVhZHkiLCJ0aGVuIiwibG9hZCIsImlzR3V0ZW5iZXJnRWRpdG9yIiwibWF5YmVTaG93Q2xhc3NpY05vdGljZSIsImJpbmRDbGFzc2ljRXZlbnRzIiwibWF5YmVTaG93R3V0ZW5iZXJnTm90aWNlIiwid3AiLCJkYXRhIiwic2VsZWN0Iiwic3Vic2NyaWJlRm9yU2l0ZUVkaXRvciIsInN1YnNjcmliZUZvckJsb2NrRWRpdG9yIiwicHJldlRpdGxlIiwiZ2V0RWRpdG9yVGl0bGUiLCJwcmV2Rm9jdXNNb2RlIiwiX3dwJGRhdGEiLCJzdWJzY3JpYmUiLCJkaXNwYXRjaCIsIl9zZWxlY3QkZ2V0RWRpdG9yU2V0dCIsImdldEVkaXRvclNldHRpbmdzIiwiZm9jdXNNb2RlIiwicmVtb3ZlTm90aWNlIiwidGl0bGUiLCJ1bnN1YnNjcmliZSIsImdldEVkaXRlZFBvc3RBdHRyaWJ1dGUiLCJpc0VkaXRQb3N0RlNFIiwiZ2V0UG9zdFRpdGxlIiwiZ2V0VG9wbW9zdEhlYWRpbmdUaXRsZSIsIl9oZWFkaW5nQmxvY2skYXR0cmlidSIsIl9oZWFkaW5nQmxvY2skYXR0cmlidTIiLCJoZWFkaW5ncyIsImdldEJsb2Nrc0J5TmFtZSIsImxlbmd0aCIsImhlYWRpbmdCbG9jayIsImdldEJsb2NrIiwiYXR0cmlidXRlcyIsImNvbnRlbnQiLCJ0ZXh0IiwiX3NlbGVjdCRnZXRQYWdlIiwiZ2V0UGFnZSIsImNvbnRleHQiLCJwb3N0VHlwZSIsIl9zZWxlY3QkZ2V0UGFnZTIiLCJfcmVmIiwiZ2V0RWRpdGVkRW50aXR5UmVjb3JkIiwicG9zdElkIiwiX3JlZiR0aXRsZSIsIiRkb2N1bWVudCIsIl8iLCJkZWJvdW5jZSIsImNsb3NlTm90aWNlIiwiYmxvY2tzIiwic2hvd0d1dGVuYmVyZ05vdGljZSIsImNyZWF0ZUluZm9Ob3RpY2UiLCJ3cGZvcm1zX2VkaXRfcG9zdF9lZHVjYXRpb24iLCJndXRlbmJlcmdfbm90aWNlIiwidGVtcGxhdGUiLCJnZXRHdXRlbmJlcmdOb3RpY2VTZXR0aW5ncyIsImhhc05vdGljZSIsInNldEludGVydmFsIiwibm90aWNlQm9keSIsIiRub3RpY2UiLCJjbG9zZXN0IiwiYWRkQ2xhc3MiLCJmaW5kIiwicmVtb3ZlQ2xhc3MiLCJkaXNtaXNzQnV0dG9uIiwidXBkYXRlVXNlck1ldGEiLCJjbGVhckludGVydmFsIiwibm90aWNlU2V0dGluZ3MiLCJpZCIsImlzRGlzbWlzc2libGUiLCJIVE1MIiwiX191bnN0YWJsZUhUTUwiLCJhY3Rpb25zIiwiY2xhc3NOYW1lIiwidmFyaWFudCIsImxhYmVsIiwiYnV0dG9uIiwiZ3V0ZW5iZXJnX2d1aWRlIiwidXJsIiwiR3VpZGUiLCJjb21wb25lbnRzIiwidXNlU3RhdGUiLCJlbGVtZW50IiwiX3dwJHBsdWdpbnMiLCJwbHVnaW5zIiwicmVnaXN0ZXJQbHVnaW4iLCJ1bnJlZ2lzdGVyUGx1Z2luIiwiR3V0ZW5iZXJnVHV0b3JpYWwiLCJfdXNlU3RhdGUiLCJfdXNlU3RhdGUyIiwiX3NsaWNlZFRvQXJyYXkiLCJpc09wZW4iLCJzZXRJc09wZW4iLCJSZWFjdCIsImNyZWF0ZUVsZW1lbnQiLCJvbkZpbmlzaCIsInBhZ2VzIiwiZ2V0R3VpZGVQYWdlcyIsIm9uQ2xpY2siLCJyZW5kZXIiLCJmb3JFYWNoIiwicGFnZSIsInB1c2giLCJGcmFnbWVudCIsImltYWdlIiwic3JjIiwiYWx0IiwiaXNUaXRsZU1hdGNoS2V5d29yZHMiLCJ2YWwiLCJ0aXRsZVZhbHVlIiwiZXhwZWN0ZWRUaXRsZVJlZ2V4IiwiUmVnRXhwIiwidGVzdCIsInJlbW92ZSIsInBvc3QiLCJhamF4X3VybCIsImFjdGlvbiIsIm5vbmNlIiwiZWR1Y2F0aW9uX25vbmNlIiwic2VjdGlvbiIsImpRdWVyeSJdLCJzb3VyY2VzIjpbImZha2VfNWE0YjFmYS5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKiBnbG9iYWwgd3Bmb3Jtc19lZGl0X3Bvc3RfZWR1Y2F0aW9uICovXG5cbi8vIG5vaW5zcGVjdGlvbiBFUzZDb252ZXJ0VmFyVG9MZXRDb25zdFxuLyoqXG4gKiBXUEZvcm1zIEVkaXQgUG9zdCBFZHVjYXRpb24gZnVuY3Rpb24uXG4gKlxuICogQHNpbmNlIDEuOC4xXG4gKi9cblxuLy8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIG5vLXZhciwgbm8tdW51c2VkLXZhcnNcbnZhciBXUEZvcm1zRWRpdFBvc3RFZHVjYXRpb24gPSB3aW5kb3cuV1BGb3Jtc0VkaXRQb3N0RWR1Y2F0aW9uIHx8ICggZnVuY3Rpb24oIGRvY3VtZW50LCB3aW5kb3csICQgKSB7XG5cdC8vIFRoZSBpZGVudGlmaWVycyBmb3IgdGhlIFJlZHV4IHN0b3Jlcy5cblx0Y29uc3QgY29yZUVkaXRTaXRlID0gJ2NvcmUvZWRpdC1zaXRlJyxcblx0XHRjb3JlRWRpdG9yID0gJ2NvcmUvZWRpdG9yJyxcblx0XHRjb3JlQmxvY2tFZGl0b3IgPSAnY29yZS9ibG9jay1lZGl0b3InLFxuXHRcdGNvcmVOb3RpY2VzID0gJ2NvcmUvbm90aWNlcycsXG5cblx0XHQvLyBIZWFkaW5nIGJsb2NrIG5hbWUuXG5cdFx0Y29yZUhlYWRpbmcgPSAnY29yZS9oZWFkaW5nJztcblxuXHQvKipcblx0ICogUHVibGljIGZ1bmN0aW9ucyBhbmQgcHJvcGVydGllcy5cblx0ICpcblx0ICogQHNpbmNlIDEuOC4xXG5cdCAqXG5cdCAqIEB0eXBlIHtPYmplY3R9XG5cdCAqL1xuXHRjb25zdCBhcHAgPSB7XG5cblx0XHQvKipcblx0XHQgKiBEZXRlcm1pbmUgaWYgdGhlIG5vdGljZSB3YXMgc2hvd24gYmVmb3JlLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOC4xXG5cdFx0ICovXG5cdFx0aXNOb3RpY2VWaXNpYmxlOiBmYWxzZSxcblxuXHRcdC8qKlxuXHRcdCAqIElkZW50aWZpZXIgZm9yIHRoZSBwbHVnaW4gYW5kIG5vdGljZS5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjkuNVxuXHRcdCAqL1xuXHRcdHBsdWdpbklkOiAnd3Bmb3Jtcy1lZGl0LXBvc3QtcHJvZHVjdC1lZHVjYXRpb24tZ3VpZGUnLFxuXG5cdFx0LyoqXG5cdFx0ICogU3RhcnQgdGhlIGVuZ2luZS5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjguMVxuXHRcdCAqL1xuXHRcdGluaXQoKSB7XG5cdFx0XHQkKCB3aW5kb3cgKS5vbiggJ2xvYWQnLCBmdW5jdGlvbigpIHtcblx0XHRcdFx0Ly8gSW4gdGhlIGNhc2Ugb2YgalF1ZXJ5IDMuKywgd2UgbmVlZCB0byB3YWl0IGZvciBhIHJlYWR5IGV2ZW50IGZpcnN0LlxuXHRcdFx0XHRpZiAoIHR5cGVvZiAkLnJlYWR5LnRoZW4gPT09ICdmdW5jdGlvbicgKSB7XG5cdFx0XHRcdFx0JC5yZWFkeS50aGVuKCBhcHAubG9hZCApO1xuXHRcdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRcdGFwcC5sb2FkKCk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogUGFnZSBsb2FkLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOC4xXG5cdFx0ICogQHNpbmNlIDEuOS41IEFkZGVkIGNvbXBhdGliaWxpdHkgZm9yIHRoZSBTaXRlIEVkaXRvci5cblx0XHQgKi9cblx0XHRsb2FkKCkge1xuXHRcdFx0aWYgKCAhIGFwcC5pc0d1dGVuYmVyZ0VkaXRvcigpICkge1xuXHRcdFx0XHRhcHAubWF5YmVTaG93Q2xhc3NpY05vdGljZSgpO1xuXHRcdFx0XHRhcHAuYmluZENsYXNzaWNFdmVudHMoKTtcblxuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGFwcC5tYXliZVNob3dHdXRlbmJlcmdOb3RpY2UoKTtcblxuXHRcdFx0Ly8gXCJjb3JlL2VkaXQtc2l0ZVwiIHN0b3JlIGF2YWlsYWJsZSBvbmx5IGluIHRoZSBTaXRlIEVkaXRvci5cblx0XHRcdGlmICggISEgd3AuZGF0YS5zZWxlY3QoIGNvcmVFZGl0U2l0ZSApICkge1xuXHRcdFx0XHRhcHAuc3Vic2NyaWJlRm9yU2l0ZUVkaXRvcigpO1xuXG5cdFx0XHRcdHJldHVybjtcblx0XHRcdH1cblxuXHRcdFx0YXBwLnN1YnNjcmliZUZvckJsb2NrRWRpdG9yKCk7XG5cdFx0fSxcblxuXHRcdC8qKlxuXHRcdCAqIFRoaXMgbWV0aG9kIGxpc3RlbnMgZm9yIGNoYW5nZXMgaW4gdGhlIFdvcmRQcmVzcyBkYXRhIHN0b3JlIGFuZCBwZXJmb3JtcyB0aGUgZm9sbG93aW5nIGFjdGlvbnM6XG5cdFx0ICogLSBNb25pdG9ycyB0aGUgZWRpdG9yIHRpdGxlIGFuZCBmb2N1cyBtb2RlIHRvIGRldGVjdCBjaGFuZ2VzLlxuXHRcdCAqIC0gRGlzbWlzc2VzIGEgY3VzdG9tIG5vdGljZSBpZiB0aGUgZm9jdXMgbW9kZSBpcyBkaXNhYmxlZCBhbmQgdGhlIG5vdGljZSBpcyB2aXNpYmxlLlxuXHRcdCAqIC0gU2hvd3MgYSBjdXN0b20gR3V0ZW5iZXJnIG5vdGljZSBpZiB0aGUgdGl0bGUgb3IgZm9jdXMgbW9kZSBjaGFuZ2VzLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOS41XG5cdFx0ICovXG5cdFx0c3Vic2NyaWJlRm9yU2l0ZUVkaXRvcigpIHtcblx0XHRcdC8vIFN0b3JlIHRoZSBpbml0aWFsIGVkaXRvciB0aXRsZSBhbmQgZm9jdXMgbW9kZSBzdGF0ZS5cblx0XHRcdGxldCBwcmV2VGl0bGUgPSBhcHAuZ2V0RWRpdG9yVGl0bGUoKTtcblx0XHRcdGxldCBwcmV2Rm9jdXNNb2RlID0gbnVsbDtcblx0XHRcdGNvbnN0IHsgc3Vic2NyaWJlLCBzZWxlY3QsIGRpc3BhdGNoIH0gPSB3cC5kYXRhO1xuXG5cdFx0XHQvLyBMaXN0ZW4gZm9yIGNoYW5nZXMgaW4gdGhlIFdvcmRQcmVzcyBkYXRhIHN0b3JlLlxuXHRcdFx0c3Vic2NyaWJlKCAoKSA9PiB7XG5cdFx0XHRcdC8vIEZldGNoIHRoZSBjdXJyZW50IGVkaXRvciBtb2RlIHNldHRpbmcuXG5cdFx0XHRcdC8vIElmIHRydWUgLSBTaXRlIEVkaXRvciBjYW52YXMgaXMgb3BlbmVkLCBhbmQgeW91IGNhbiBlZGl0IHNvbWV0aGluZy5cblx0XHRcdFx0Ly8gSWYgZmFsc2UgLSB5b3Ugc2hvdWxkIHNlZSB0aGUgc2lkZWJhciB3aXRoIG5hdmlnYXRpb24gYW5kIHByZXZpZXdcblx0XHRcdFx0Ly8gd2l0aCBzZWxlY3RlZCB0ZW1wbGF0ZSBvciBwYWdlLlxuXHRcdFx0XHRjb25zdCB7IGZvY3VzTW9kZSB9ID0gc2VsZWN0KCBjb3JlRWRpdG9yICkuZ2V0RWRpdG9yU2V0dGluZ3MoKTtcblxuXHRcdFx0XHQvLyBJZiBmb2N1cyBtb2RlIGlzIGRpc2FibGVkIGFuZCBhIG5vdGljZSBpcyB2aXNpYmxlLCByZW1vdmUgdGhlIG5vdGljZS5cblx0XHRcdFx0Ly8gVGhpcyBpcyBlc3NlbnRpYWwgYmVjYXVzZSB1c2VyIGNhbiBzd2l0Y2ggcGFnZXMgLyB0ZW1wbGF0ZXNcblx0XHRcdFx0Ly8gd2l0aG91dCBhIHBhZ2UtcmVsb2FkLlxuXHRcdFx0XHRpZiAoICEgZm9jdXNNb2RlICYmIGFwcC5pc05vdGljZVZpc2libGUgKSB7XG5cdFx0XHRcdFx0YXBwLmlzTm90aWNlVmlzaWJsZSA9IGZhbHNlO1xuXHRcdFx0XHRcdHByZXZGb2N1c01vZGUgPSBmb2N1c01vZGU7XG5cblx0XHRcdFx0XHRkaXNwYXRjaCggY29yZU5vdGljZXMgKS5yZW1vdmVOb3RpY2UoIGFwcC5wbHVnaW5JZCApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Y29uc3QgdGl0bGUgPSBhcHAuZ2V0RWRpdG9yVGl0bGUoKTtcblxuXHRcdFx0XHQvLyBJZiBuZWl0aGVyIHRoZSB0aXRsZSBub3IgdGhlIGZvY3VzIG1vZGUgaGFzIGNoYW5nZWQsIGRvIG5vdGhpbmcuXG5cdFx0XHRcdGlmICggcHJldlRpdGxlID09PSB0aXRsZSAmJiBwcmV2Rm9jdXNNb2RlID09PSBmb2N1c01vZGUgKSB7XG5cdFx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Ly8gVXBkYXRlIHRoZSBwcmV2aW91cyB0aXRsZSBhbmQgZm9jdXMgbW9kZSB2YWx1ZXMgZm9yIHRoZSBuZXh0IHN1YnNjcmlwdGlvbiBjeWNsZS5cblx0XHRcdFx0cHJldlRpdGxlID0gdGl0bGU7XG5cdFx0XHRcdHByZXZGb2N1c01vZGUgPSBmb2N1c01vZGU7XG5cblx0XHRcdFx0Ly8gU2hvdyBhIGN1c3RvbSBHdXRlbmJlcmcgbm90aWNlIGlmIGNvbmRpdGlvbnMgYXJlIG1ldC5cblx0XHRcdFx0YXBwLm1heWJlU2hvd0d1dGVuYmVyZ05vdGljZSgpO1xuXHRcdFx0fSApO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBTdWJzY3JpYmVzIHRvIGNoYW5nZXMgaW4gdGhlIFdvcmRQcmVzcyBibG9jayBlZGl0b3IgYW5kIG1vbml0b3JzIHRoZSBlZGl0b3IncyB0aXRsZS5cblx0XHQgKiBXaGVuIHRoZSB0aXRsZSBjaGFuZ2VzLCBpdCB0cmlnZ2VycyBhIHByb2Nlc3MgdG8gcG90ZW50aWFsbHkgZGlzcGxheSBhIEd1dGVuYmVyZyBub3RpY2UuXG5cdFx0ICogVGhlIHN1YnNjcmlwdGlvbiBpcyBhdXRvbWF0aWNhbGx5IHN0b3BwZWQgaWYgdGhlIG5vdGljZSBiZWNvbWVzIHZpc2libGUuXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS45LjVcblx0XHQgKi9cblx0XHRzdWJzY3JpYmVGb3JCbG9ja0VkaXRvcigpIHtcblx0XHRcdGxldCBwcmV2VGl0bGUgPSBhcHAuZ2V0RWRpdG9yVGl0bGUoKTtcblx0XHRcdGNvbnN0IHsgc3Vic2NyaWJlIH0gPSB3cC5kYXRhO1xuXG5cdFx0XHQvLyBTdWJzY3JpYmUgdG8gV29yZFByZXNzIGRhdGEgY2hhbmdlcy5cblx0XHRcdGNvbnN0IHVuc3Vic2NyaWJlID0gc3Vic2NyaWJlKCAoKSA9PiB7XG5cdFx0XHRcdGNvbnN0IHRpdGxlID0gYXBwLmdldEVkaXRvclRpdGxlKCk7XG5cblx0XHRcdFx0Ly8gQ2hlY2sgaWYgdGhlIHRpdGxlIGhhcyBjaGFuZ2VkIHNpbmNlIHRoZSBwcmV2aW91cyB2YWx1ZS5cblx0XHRcdFx0aWYgKCBwcmV2VGl0bGUgPT09IHRpdGxlICkge1xuXHRcdFx0XHRcdHJldHVybjtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdC8vIFVwZGF0ZSB0aGUgcHJldmlvdXMgdGl0bGUgdG8gdGhlIGN1cnJlbnQgdGl0bGUuXG5cdFx0XHRcdHByZXZUaXRsZSA9IHRpdGxlO1xuXG5cdFx0XHRcdGFwcC5tYXliZVNob3dHdXRlbmJlcmdOb3RpY2UoKTtcblxuXHRcdFx0XHQvLyBJZiB0aGUgbm90aWNlIGlzIHZpc2libGUsIHN0b3AgdGhlIFdvcmRQcmVzcyBkYXRhIHN1YnNjcmlwdGlvbi5cblx0XHRcdFx0aWYgKCBhcHAuaXNOb3RpY2VWaXNpYmxlICkge1xuXHRcdFx0XHRcdHVuc3Vic2NyaWJlKCk7XG5cdFx0XHRcdH1cblx0XHRcdH0gKTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogUmV0cmlldmVzIHRoZSB0aXRsZSBvZiB0aGUgcG9zdCBjdXJyZW50bHkgYmVpbmcgZWRpdGVkLiBJZiBpbiB0aGUgU2l0ZSBFZGl0b3IsXG5cdFx0ICogaXQgYXR0ZW1wdHMgdG8gZmV0Y2ggdGhlIHRpdGxlIGZyb20gdGhlIHRvcG1vc3QgaGVhZGluZyBibG9jay4gT3RoZXJ3aXNlLCBpdFxuXHRcdCAqIHJldHJpZXZlcyB0aGUgdGl0bGUgYXR0cmlidXRlIG9mIHRoZSBlZGl0ZWQgcG9zdC5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjkuNVxuXHRcdCAqXG5cdFx0ICogQHJldHVybiB7c3RyaW5nfSBUaGUgcG9zdCB0aXRsZSBvciBhbiBlbXB0eSBzdHJpbmcgaWYgbm8gdGl0bGUgaXMgZm91bmQuXG5cdFx0ICovXG5cdFx0Z2V0RWRpdG9yVGl0bGUoKSB7XG5cdFx0XHRjb25zdCB7IHNlbGVjdCB9ID0gd3AuZGF0YTtcblxuXHRcdFx0Ly8gUmV0cmlldmUgdGhlIHRpdGxlIGZvciBQb3N0IEVkaXRvci5cblx0XHRcdGlmICggISBzZWxlY3QoIGNvcmVFZGl0U2l0ZSApICkge1xuXHRcdFx0XHRyZXR1cm4gc2VsZWN0KCBjb3JlRWRpdG9yICkuZ2V0RWRpdGVkUG9zdEF0dHJpYnV0ZSggJ3RpdGxlJyApO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoIGFwcC5pc0VkaXRQb3N0RlNFKCkgKSB7XG5cdFx0XHRcdHJldHVybiBhcHAuZ2V0UG9zdFRpdGxlKCk7XG5cdFx0XHR9XG5cblx0XHRcdHJldHVybiBhcHAuZ2V0VG9wbW9zdEhlYWRpbmdUaXRsZSgpO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBSZXRyaWV2ZXMgdGhlIGNvbnRlbnQgb2YgdGhlIGZpcnN0IGhlYWRpbmcgYmxvY2suXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS45LjVcblx0XHQgKlxuXHRcdCAqIEByZXR1cm4ge3N0cmluZ30gVGhlIHRvcG1vc3QgaGVhZGluZyBjb250ZW50IG9yIG51bGwgaWYgbm90IGZvdW5kLlxuXHRcdCAqL1xuXHRcdGdldFRvcG1vc3RIZWFkaW5nVGl0bGUoKSB7XG5cdFx0XHRjb25zdCB7IHNlbGVjdCB9ID0gd3AuZGF0YTtcblxuXHRcdFx0Y29uc3QgaGVhZGluZ3MgPSBzZWxlY3QoIGNvcmVCbG9ja0VkaXRvciApLmdldEJsb2Nrc0J5TmFtZSggY29yZUhlYWRpbmcgKTtcblxuXHRcdFx0aWYgKCAhIGhlYWRpbmdzLmxlbmd0aCApIHtcblx0XHRcdFx0cmV0dXJuICcnO1xuXHRcdFx0fVxuXG5cdFx0XHRjb25zdCBoZWFkaW5nQmxvY2sgPSBzZWxlY3QoIGNvcmVCbG9ja0VkaXRvciApLmdldEJsb2NrKCBoZWFkaW5nc1sgMCBdICk7XG5cblx0XHRcdHJldHVybiBoZWFkaW5nQmxvY2s/LmF0dHJpYnV0ZXM/LmNvbnRlbnQ/LnRleHQgPz8gJyc7XG5cdFx0fSxcblxuXHRcdC8qKlxuXHRcdCAqIERldGVybWluZXMgaWYgdGhlIGN1cnJlbnQgZWRpdGluZyBjb250ZXh0IGlzIGZvciBhIHBvc3QgdHlwZSBpbiB0aGUgRnVsbCBTaXRlIEVkaXRvciAoRlNFKS5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjkuNVxuXHRcdCAqXG5cdFx0ICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgY3VycmVudCBjb250ZXh0IHJlcHJlc2VudHMgYSBwb3N0IHR5cGUgaW4gdGhlIEZTRSwgb3RoZXJ3aXNlIGZhbHNlLlxuXHRcdCAqL1xuXHRcdGlzRWRpdFBvc3RGU0UoKSB7XG5cdFx0XHRjb25zdCB7IHNlbGVjdCB9ID0gd3AuZGF0YTtcblx0XHRcdGNvbnN0IHsgY29udGV4dCB9ID0gc2VsZWN0KCBjb3JlRWRpdFNpdGUgKS5nZXRQYWdlKCk7XG5cblx0XHRcdHJldHVybiAhISBjb250ZXh0Py5wb3N0VHlwZTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogUmV0cmlldmVzIHRoZSB0aXRsZSBvZiBhIHBvc3QgYmFzZWQgb24gaXRzIHR5cGUgYW5kIElEIGZyb20gdGhlIGN1cnJlbnQgZWRpdGluZyBjb250ZXh0LlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOS41XG5cdFx0ICpcblx0XHQgKiBAcmV0dXJuIHtzdHJpbmd9IFRoZSB0aXRsZSBvZiB0aGUgcG9zdC5cblx0XHQgKi9cblx0XHRnZXRQb3N0VGl0bGUoKSB7XG5cdFx0XHRjb25zdCB7IHNlbGVjdCB9ID0gd3AuZGF0YTtcblx0XHRcdGNvbnN0IHsgY29udGV4dCB9ID0gc2VsZWN0KCBjb3JlRWRpdFNpdGUgKS5nZXRQYWdlKCk7XG5cblx0XHRcdC8vIFVzZSBgZ2V0RWRpdGVkRW50aXR5UmVjb3JkYCBpbnN0ZWFkIG9mIGBnZXRFbnRpdHlSZWNvcmRgXG5cdFx0XHQvLyB0byBmZXRjaCB0aGUgbGl2ZSwgdXBkYXRlZCBkYXRhIGZvciB0aGUgcG9zdCBiZWluZyBlZGl0ZWQuXG5cdFx0XHRjb25zdCB7IHRpdGxlID0gJycgfSA9IHNlbGVjdCggJ2NvcmUnICkuZ2V0RWRpdGVkRW50aXR5UmVjb3JkKFxuXHRcdFx0XHQncG9zdFR5cGUnLFxuXHRcdFx0XHRjb250ZXh0LnBvc3RUeXBlLFxuXHRcdFx0XHRjb250ZXh0LnBvc3RJZFxuXHRcdFx0KSB8fCB7fTtcblxuXHRcdFx0cmV0dXJuIHRpdGxlO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBCaW5kIGV2ZW50cyBmb3IgQ2xhc3NpYyBFZGl0b3IuXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS44LjFcblx0XHQgKi9cblx0XHRiaW5kQ2xhc3NpY0V2ZW50cygpIHtcblx0XHRcdGNvbnN0ICRkb2N1bWVudCA9ICQoIGRvY3VtZW50ICk7XG5cblx0XHRcdGlmICggISBhcHAuaXNOb3RpY2VWaXNpYmxlICkge1xuXHRcdFx0XHQkZG9jdW1lbnQub24oICdpbnB1dCcsICcjdGl0bGUnLCBfLmRlYm91bmNlKCBhcHAubWF5YmVTaG93Q2xhc3NpY05vdGljZSwgMTAwMCApICk7XG5cdFx0XHR9XG5cblx0XHRcdCRkb2N1bWVudC5vbiggJ2NsaWNrJywgJy53cGZvcm1zLWVkaXQtcG9zdC1lZHVjYXRpb24tbm90aWNlLWNsb3NlJywgYXBwLmNsb3NlTm90aWNlICk7XG5cdFx0fSxcblxuXHRcdC8qKlxuXHRcdCAqIERldGVybWluZSBpZiB0aGUgZWRpdG9yIGlzIEd1dGVuYmVyZy5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjguMVxuXHRcdCAqXG5cdFx0ICogQHJldHVybiB7Ym9vbGVhbn0gVHJ1ZSBpZiB0aGUgZWRpdG9yIGlzIEd1dGVuYmVyZy5cblx0XHQgKi9cblx0XHRpc0d1dGVuYmVyZ0VkaXRvcigpIHtcblx0XHRcdHJldHVybiB0eXBlb2Ygd3AgIT09ICd1bmRlZmluZWQnICYmIHR5cGVvZiB3cC5ibG9ja3MgIT09ICd1bmRlZmluZWQnO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBDcmVhdGUgYSBub3RpY2UgZm9yIEd1dGVuYmVyZy5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjguMVxuXHRcdCAqL1xuXHRcdHNob3dHdXRlbmJlcmdOb3RpY2UoKSB7XG5cdFx0XHR3cC5kYXRhLmRpc3BhdGNoKCBjb3JlTm90aWNlcyApLmNyZWF0ZUluZm9Ob3RpY2UoXG5cdFx0XHRcdHdwZm9ybXNfZWRpdF9wb3N0X2VkdWNhdGlvbi5ndXRlbmJlcmdfbm90aWNlLnRlbXBsYXRlLFxuXHRcdFx0XHRhcHAuZ2V0R3V0ZW5iZXJnTm90aWNlU2V0dGluZ3MoKVxuXHRcdFx0KTtcblxuXHRcdFx0Ly8gVGhlIG5vdGljZSBjb21wb25lbnQgZG9lc24ndCBoYXZlIGEgd2F5IHRvIGFkZCBIVE1MIGlkIG9yIGNsYXNzIHRvIHRoZSBub3RpY2UuXG5cdFx0XHQvLyBBbHNvLCB0aGUgbm90aWNlIGJlY2FtZSB2aXNpYmxlIHdpdGggYSBkZWxheSBvbiBvbGQgR3V0ZW5iZXJnIHZlcnNpb25zLlxuXHRcdFx0Y29uc3QgaGFzTm90aWNlID0gc2V0SW50ZXJ2YWwoIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRjb25zdCBub3RpY2VCb2R5ID0gJCggJy53cGZvcm1zLWVkaXQtcG9zdC1lZHVjYXRpb24tbm90aWNlLWJvZHknICk7XG5cdFx0XHRcdGlmICggISBub3RpY2VCb2R5Lmxlbmd0aCApIHtcblx0XHRcdFx0XHRyZXR1cm47XG5cdFx0XHRcdH1cblxuXHRcdFx0XHRjb25zdCAkbm90aWNlID0gbm90aWNlQm9keS5jbG9zZXN0KCAnLmNvbXBvbmVudHMtbm90aWNlJyApO1xuXHRcdFx0XHQkbm90aWNlLmFkZENsYXNzKCAnd3Bmb3Jtcy1lZGl0LXBvc3QtZWR1Y2F0aW9uLW5vdGljZScgKTtcblx0XHRcdFx0JG5vdGljZS5maW5kKCAnLmlzLXNlY29uZGFyeSwgLmlzLWxpbmsnICkucmVtb3ZlQ2xhc3MoICdpcy1zZWNvbmRhcnknICkucmVtb3ZlQ2xhc3MoICdpcy1saW5rJyApLmFkZENsYXNzKCAnaXMtcHJpbWFyeScgKTtcblxuXHRcdFx0XHQvLyBXZSBjYW4ndCB1c2Ugb25EaXNtaXNzIGNhbGxiYWNrIGFzIGl0IHdhcyBpbnRyb2R1Y2VkIGluIFdvcmRQcmVzcyA2LjAgb25seS5cblx0XHRcdFx0Y29uc3QgZGlzbWlzc0J1dHRvbiA9ICRub3RpY2UuZmluZCggJy5jb21wb25lbnRzLW5vdGljZV9fZGlzbWlzcycgKTtcblx0XHRcdFx0aWYgKCBkaXNtaXNzQnV0dG9uICkge1xuXHRcdFx0XHRcdGRpc21pc3NCdXR0b24ub24oICdjbGljaycsIGZ1bmN0aW9uKCkge1xuXHRcdFx0XHRcdFx0YXBwLnVwZGF0ZVVzZXJNZXRhKCk7XG5cdFx0XHRcdFx0fSApO1xuXHRcdFx0XHR9XG5cblx0XHRcdFx0Y2xlYXJJbnRlcnZhbCggaGFzTm90aWNlICk7XG5cdFx0XHR9LCAxMDAgKTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogR2V0IHNldHRpbmdzIGZvciB0aGUgR3V0ZW5iZXJnIG5vdGljZS5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjguMVxuXHRcdCAqXG5cdFx0ICogQHJldHVybiB7T2JqZWN0fSBOb3RpY2Ugc2V0dGluZ3MuXG5cdFx0ICovXG5cdFx0Z2V0R3V0ZW5iZXJnTm90aWNlU2V0dGluZ3MoKSB7XG5cdFx0XHRjb25zdCBub3RpY2VTZXR0aW5ncyA9IHtcblx0XHRcdFx0aWQ6IGFwcC5wbHVnaW5JZCxcblx0XHRcdFx0aXNEaXNtaXNzaWJsZTogdHJ1ZSxcblx0XHRcdFx0SFRNTDogdHJ1ZSxcblx0XHRcdFx0X191bnN0YWJsZUhUTUw6IHRydWUsXG5cdFx0XHRcdGFjdGlvbnM6IFtcblx0XHRcdFx0XHR7XG5cdFx0XHRcdFx0XHRjbGFzc05hbWU6ICd3cGZvcm1zLWVkaXQtcG9zdC1lZHVjYXRpb24tbm90aWNlLWd1aWRlLWJ1dHRvbicsXG5cdFx0XHRcdFx0XHR2YXJpYW50OiAncHJpbWFyeScsXG5cdFx0XHRcdFx0XHRsYWJlbDogd3Bmb3Jtc19lZGl0X3Bvc3RfZWR1Y2F0aW9uLmd1dGVuYmVyZ19ub3RpY2UuYnV0dG9uLFxuXHRcdFx0XHRcdH0sXG5cdFx0XHRcdF0sXG5cdFx0XHR9O1xuXG5cdFx0XHRpZiAoICEgd3Bmb3Jtc19lZGl0X3Bvc3RfZWR1Y2F0aW9uLmd1dGVuYmVyZ19ndWlkZSApIHtcblx0XHRcdFx0bm90aWNlU2V0dGluZ3MuYWN0aW9uc1sgMCBdLnVybCA9IHdwZm9ybXNfZWRpdF9wb3N0X2VkdWNhdGlvbi5ndXRlbmJlcmdfbm90aWNlLnVybDtcblxuXHRcdFx0XHRyZXR1cm4gbm90aWNlU2V0dGluZ3M7XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IHsgR3VpZGUgfSA9IHdwLmNvbXBvbmVudHMsXG5cdFx0XHRcdHsgdXNlU3RhdGUgfSA9IHdwLmVsZW1lbnQsXG5cdFx0XHRcdHsgcmVnaXN0ZXJQbHVnaW4sIHVucmVnaXN0ZXJQbHVnaW4gfSA9IHdwLnBsdWdpbnM7XG5cblx0XHRcdGNvbnN0IEd1dGVuYmVyZ1R1dG9yaWFsID0gZnVuY3Rpb24oKSB7XG5cdFx0XHRcdGNvbnN0IFsgaXNPcGVuLCBzZXRJc09wZW4gXSA9IHVzZVN0YXRlKCB0cnVlICk7XG5cblx0XHRcdFx0aWYgKCAhIGlzT3BlbiApIHtcblx0XHRcdFx0XHRyZXR1cm4gbnVsbDtcblx0XHRcdFx0fVxuXG5cdFx0XHRcdHJldHVybiAoXG5cdFx0XHRcdFx0Ly8gZXNsaW50LWRpc2FibGUtbmV4dC1saW5lIHJlYWN0L3JlYWN0LWluLWpzeC1zY29wZVxuXHRcdFx0XHRcdDxHdWlkZVxuXHRcdFx0XHRcdFx0Y2xhc3NOYW1lPVwiZWRpdC1wb3N0LXdlbGNvbWUtZ3VpZGVcIlxuXHRcdFx0XHRcdFx0b25GaW5pc2g9eyAoKSA9PiB7XG5cdFx0XHRcdFx0XHRcdHVucmVnaXN0ZXJQbHVnaW4oIGFwcC5wbHVnaW5JZCApO1xuXHRcdFx0XHRcdFx0XHRzZXRJc09wZW4oIGZhbHNlICk7XG5cdFx0XHRcdFx0XHR9IH1cblx0XHRcdFx0XHRcdHBhZ2VzPXsgYXBwLmdldEd1aWRlUGFnZXMoKSB9XG5cdFx0XHRcdFx0Lz5cblx0XHRcdFx0KTtcblx0XHRcdH07XG5cblx0XHRcdG5vdGljZVNldHRpbmdzLmFjdGlvbnNbIDAgXS5vbkNsaWNrID0gKCkgPT4gcmVnaXN0ZXJQbHVnaW4oIGFwcC5wbHVnaW5JZCwgeyByZW5kZXI6IEd1dGVuYmVyZ1R1dG9yaWFsIH0gKTtcblxuXHRcdFx0cmV0dXJuIG5vdGljZVNldHRpbmdzO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBHZXQgR3VpZGUgcGFnZXMgaW4gcHJvcGVyIGZvcm1hdC5cblx0XHQgKlxuXHRcdCAqIEBzaW5jZSAxLjguMVxuXHRcdCAqXG5cdFx0ICogQHJldHVybiB7QXJyYXl9IEd1aWRlIFBhZ2VzLlxuXHRcdCAqL1xuXHRcdGdldEd1aWRlUGFnZXMoKSB7XG5cdFx0XHRjb25zdCBwYWdlcyA9IFtdO1xuXG5cdFx0XHR3cGZvcm1zX2VkaXRfcG9zdF9lZHVjYXRpb24uZ3V0ZW5iZXJnX2d1aWRlLmZvckVhY2goIGZ1bmN0aW9uKCBwYWdlICkge1xuXHRcdFx0XHRwYWdlcy5wdXNoKFxuXHRcdFx0XHRcdHtcblx0XHRcdFx0XHRcdC8qIGVzbGludC1kaXNhYmxlIHJlYWN0L3JlYWN0LWluLWpzeC1zY29wZSAqL1xuXHRcdFx0XHRcdFx0Y29udGVudDogKFxuXHRcdFx0XHRcdFx0XHQ8PlxuXHRcdFx0XHRcdFx0XHRcdDxoMSBjbGFzc05hbWU9XCJlZGl0LXBvc3Qtd2VsY29tZS1ndWlkZV9faGVhZGluZ1wiPnsgcGFnZS50aXRsZSB9PC9oMT5cblx0XHRcdFx0XHRcdFx0XHQ8cCBjbGFzc05hbWU9XCJlZGl0LXBvc3Qtd2VsY29tZS1ndWlkZV9fdGV4dFwiPnsgcGFnZS5jb250ZW50IH08L3A+XG5cdFx0XHRcdFx0XHRcdDwvPlxuXHRcdFx0XHRcdFx0KSxcblx0XHRcdFx0XHRcdGltYWdlOiA8aW1nIGNsYXNzTmFtZT1cImVkaXQtcG9zdC13ZWxjb21lLWd1aWRlX19pbWFnZVwiIHNyYz17IHBhZ2UuaW1hZ2UgfSBhbHQ9eyBwYWdlLnRpdGxlIH0gLz4sXG5cdFx0XHRcdFx0XHQvKiBlc2xpbnQtZW5hYmxlIHJlYWN0L3JlYWN0LWluLWpzeC1zY29wZSAqL1xuXHRcdFx0XHRcdH1cblx0XHRcdFx0KTtcblx0XHRcdH0gKTtcblxuXHRcdFx0cmV0dXJuIHBhZ2VzO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBTaG93IG5vdGljZSBpZiB0aGUgcGFnZSB0aXRsZSBtYXRjaGVzIHNvbWUga2V5d29yZHMgZm9yIENsYXNzaWMgRWRpdG9yLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOC4xXG5cdFx0ICovXG5cdFx0bWF5YmVTaG93Q2xhc3NpY05vdGljZSgpIHtcblx0XHRcdGlmICggYXBwLmlzTm90aWNlVmlzaWJsZSApIHtcblx0XHRcdFx0cmV0dXJuO1xuXHRcdFx0fVxuXG5cdFx0XHRpZiAoIGFwcC5pc1RpdGxlTWF0Y2hLZXl3b3JkcyggJCggJyN0aXRsZScgKS52YWwoKSApICkge1xuXHRcdFx0XHRhcHAuaXNOb3RpY2VWaXNpYmxlID0gdHJ1ZTtcblxuXHRcdFx0XHQkKCAnLndwZm9ybXMtZWRpdC1wb3N0LWVkdWNhdGlvbi1ub3RpY2UnICkucmVtb3ZlQ2xhc3MoICd3cGZvcm1zLWhpZGRlbicgKTtcblx0XHRcdH1cblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogU2hvdyBub3RpY2UgaWYgdGhlIHBhZ2UgdGl0bGUgbWF0Y2hlcyBzb21lIGtleXdvcmRzIGZvciBHdXRlbmJlcmcgRWRpdG9yLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOC4xXG5cdFx0ICovXG5cdFx0bWF5YmVTaG93R3V0ZW5iZXJnTm90aWNlKCkge1xuXHRcdFx0aWYgKCBhcHAuaXNOb3RpY2VWaXNpYmxlICkge1xuXHRcdFx0XHRyZXR1cm47XG5cdFx0XHR9XG5cblx0XHRcdGNvbnN0IHRpdGxlID0gYXBwLmdldEVkaXRvclRpdGxlKCk7XG5cblx0XHRcdGlmICggYXBwLmlzVGl0bGVNYXRjaEtleXdvcmRzKCB0aXRsZSApICkge1xuXHRcdFx0XHRhcHAuaXNOb3RpY2VWaXNpYmxlID0gdHJ1ZTtcblxuXHRcdFx0XHRhcHAuc2hvd0d1dGVuYmVyZ05vdGljZSgpO1xuXHRcdFx0fVxuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBEZXRlcm1pbmUgaWYgdGhlIHRpdGxlIG1hdGNoZXMga2V5d29yZHMuXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS44LjFcblx0XHQgKlxuXHRcdCAqIEBwYXJhbSB7c3RyaW5nfSB0aXRsZVZhbHVlIFBhZ2UgdGl0bGUgdmFsdWUuXG5cdFx0ICpcblx0XHQgKiBAcmV0dXJuIHtib29sZWFufSBUcnVlIGlmIHRoZSB0aXRsZSBtYXRjaGVzIHNvbWUga2V5d29yZHMuXG5cdFx0ICovXG5cdFx0aXNUaXRsZU1hdGNoS2V5d29yZHMoIHRpdGxlVmFsdWUgKSB7XG5cdFx0XHRjb25zdCBleHBlY3RlZFRpdGxlUmVnZXggPSBuZXcgUmVnRXhwKCAvXFxiKGNvbnRhY3R8Zm9ybSlcXGIvaSApO1xuXG5cdFx0XHRyZXR1cm4gZXhwZWN0ZWRUaXRsZVJlZ2V4LnRlc3QoIHRpdGxlVmFsdWUgKTtcblx0XHR9LFxuXG5cdFx0LyoqXG5cdFx0ICogQ2xvc2UgYSBub3RpY2UuXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS44LjFcblx0XHQgKi9cblx0XHRjbG9zZU5vdGljZSgpIHtcblx0XHRcdCQoIHRoaXMgKS5jbG9zZXN0KCAnLndwZm9ybXMtZWRpdC1wb3N0LWVkdWNhdGlvbi1ub3RpY2UnICkucmVtb3ZlKCk7XG5cblx0XHRcdGFwcC51cGRhdGVVc2VyTWV0YSgpO1xuXHRcdH0sXG5cblx0XHQvKipcblx0XHQgKiBVcGRhdGUgdXNlciBtZXRhIGFuZCBkb24ndCBzaG93IHRoZSBub3RpY2UgbmV4dCB0aW1lLlxuXHRcdCAqXG5cdFx0ICogQHNpbmNlIDEuOC4xXG5cdFx0ICovXG5cdFx0dXBkYXRlVXNlck1ldGEoKSB7XG5cdFx0XHQkLnBvc3QoXG5cdFx0XHRcdHdwZm9ybXNfZWRpdF9wb3N0X2VkdWNhdGlvbi5hamF4X3VybCxcblx0XHRcdFx0e1xuXHRcdFx0XHRcdGFjdGlvbjogJ3dwZm9ybXNfZWR1Y2F0aW9uX2Rpc21pc3MnLFxuXHRcdFx0XHRcdG5vbmNlOiB3cGZvcm1zX2VkaXRfcG9zdF9lZHVjYXRpb24uZWR1Y2F0aW9uX25vbmNlLFxuXHRcdFx0XHRcdHNlY3Rpb246ICdlZGl0LXBvc3Qtbm90aWNlJyxcblx0XHRcdFx0fVxuXHRcdFx0KTtcblx0XHR9LFxuXHR9O1xuXG5cdHJldHVybiBhcHA7XG59KCBkb2N1bWVudCwgd2luZG93LCBqUXVlcnkgKSApO1xuXG5XUEZvcm1zRWRpdFBvc3RFZHVjYXRpb24uaW5pdCgpO1xuIl0sIm1hcHBpbmdzIjoiOzs7Ozs7OztBQUFBOztBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUFFQTtBQUNBLElBQUlBLHdCQUF3QixHQUFHQyxNQUFNLENBQUNELHdCQUF3QixJQUFNLFVBQVVFLFFBQVEsRUFBRUQsTUFBTSxFQUFFRSxDQUFDLEVBQUc7RUFDbkc7RUFDQSxJQUFNQyxZQUFZLEdBQUcsZ0JBQWdCO0lBQ3BDQyxVQUFVLEdBQUcsYUFBYTtJQUMxQkMsZUFBZSxHQUFHLG1CQUFtQjtJQUNyQ0MsV0FBVyxHQUFHLGNBQWM7SUFFNUI7SUFDQUMsV0FBVyxHQUFHLGNBQWM7O0VBRTdCO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0VBQ0MsSUFBTUMsR0FBRyxHQUFHO0lBRVg7QUFDRjtBQUNBO0FBQ0E7QUFDQTtJQUNFQyxlQUFlLEVBQUUsS0FBSztJQUV0QjtBQUNGO0FBQ0E7QUFDQTtBQUNBO0lBQ0VDLFFBQVEsRUFBRSwyQ0FBMkM7SUFFckQ7QUFDRjtBQUNBO0FBQ0E7QUFDQTtJQUNFQyxJQUFJLFdBQUpBLElBQUlBLENBQUEsRUFBRztNQUNOVCxDQUFDLENBQUVGLE1BQU8sQ0FBQyxDQUFDWSxFQUFFLENBQUUsTUFBTSxFQUFFLFlBQVc7UUFDbEM7UUFDQSxJQUFLLE9BQU9WLENBQUMsQ0FBQ1csS0FBSyxDQUFDQyxJQUFJLEtBQUssVUFBVSxFQUFHO1VBQ3pDWixDQUFDLENBQUNXLEtBQUssQ0FBQ0MsSUFBSSxDQUFFTixHQUFHLENBQUNPLElBQUssQ0FBQztRQUN6QixD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
},{}]},{},[1])