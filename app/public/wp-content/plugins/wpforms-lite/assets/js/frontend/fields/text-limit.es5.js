(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
"use strict";

function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
/* global wpforms_settings */

(function () {
  /**
   * Predefine hint text to display.
   *
   * @since 1.5.6
   * @since 1.6.4 Added a new macros - {remaining}.
   *
   * @param {string} hintText Hint text.
   * @param {number} count    Current count.
   * @param {number} limit    Limit to.
   *
   * @return {string} Predefined hint text.
   */
  function renderHint(hintText, count, limit) {
    return hintText.replace('{count}', count).replace('{limit}', limit).replace('{remaining}', limit - count);
  }

  /**
   * Create HTMLElement hint element with text.
   *
   * @since 1.5.6
   *
   * @param {number|string} formId  Form id.
   * @param {number|string} fieldId Form field id.
   * @param {string}        text    Hint text.
   *
   * @return {Object} HTMLElement hint element with text.
   */
  function createHint(formId, fieldId, text) {
    var hint = document.createElement('div');
    formId = _typeof(formId) === 'object' ? '' : formId;
    fieldId = _typeof(fieldId) === 'object' ? '' : fieldId;
    hint.classList.add('wpforms-field-limit-text');
    hint.id = 'wpforms-field-limit-text-' + formId + '-' + fieldId;
    hint.setAttribute('aria-live', 'polite');
    hint.textContent = text;
    return hint;
  }

  /**
   * Keyup/Keydown event higher order function for characters limit.
   *
   * @since 1.5.6
   *
   * @param {Object} hint  HTMLElement hint element.
   * @param {number} limit Max allowed number of characters.
   *
   * @return {Function} Handler function.
   */
  function checkCharacters(hint, limit) {
    // noinspection JSUnusedLocalSymbols
    return function (e) {
      // eslint-disable-line no-unused-vars
      hint.textContent = renderHint(window.wpforms_settings.val_limit_characters, this.value.length, limit);
    };
  }

  /**
   * Count words in the string.
   *
   * @since 1.6.2
   *
   * @param {string} string String value.
   *
   * @return {number} Words count.
   */
  function countWords(string) {
    if (typeof string !== 'string') {
      return 0;
    }
    if (!string.length) {
      return 0;
    }
    [/([A-Z]+),([A-Z]+)/gi, /([0-9]+),([A-Z]+)/gi, /([A-Z]+),([0-9]+)/gi].forEach(function (pattern) {
      string = string.replace(pattern, '$1, $2');
    });
    return string.split(/\s+/).length;
  }

  /**
   * Keyup/Keydown event higher order function for words limit.
   *
   * @since 1.5.6
   *
   * @param {Object} hint  HTMLElement hint element.
   * @param {number} limit Max allowed number of characters.
   *
   * @return {Function} Handler function.
   */
  function checkWords(hint, limit) {
    return function (e) {
      var value = this.value.trim(),
        words = countWords(value);
      hint.textContent = renderHint(window.wpforms_settings.val_limit_words, words, limit);

      // We should prevent the keys: Enter, Space, Comma.
      if ([13, 32, 188].indexOf(e.keyCode) > -1 && words >= limit) {
        e.preventDefault();
      }
    };
  }

  /**
   * Get passed text from the clipboard.
   *
   * @since 1.5.6
   *
   * @param {ClipboardEvent} e Clipboard event.
   *
   * @return {string} Text from clipboard.
   */
  function getPastedText(e) {
    if (window.clipboardData && window.clipboardData.getData) {
      // IE
      return window.clipboardData.getData('Text');
    } else if (e.clipboardData && e.clipboardData.getData) {
      return e.clipboardData.getData('text/plain');
    }
    return '';
  }

  /**
   * Paste event higher order function for character limit.
   *
   * @since 1.6.7.1
   *
   * @param {number} limit Max allowed number of characters.
   *
   * @return {Function} Event handler.
   */
  function pasteText(limit) {
    return function (e) {
      e.preventDefault();
      var pastedText = getPastedText(e),
        newPosition = this.selectionStart + pastedText.length,
        newText = this.value.substring(0, this.selectionStart) + pastedText + this.value.substring(this.selectionStart);
      this.value = newText.substring(0, limit);
      this.setSelectionRange(newPosition, newPosition);
    };
  }

  /**
   * Limit string length to a certain number of words, preserving line breaks.
   *
   * @since 1.6.8
   *
   * @param {string} text  Text.
   * @param {number} limit Max allowed number of words.
   *
   * @return {string} Text with the limited number of words.
   */
  function limitWords(text, limit) {
    var result = '';

    // Regular expression pattern: match any space character.
    var regEx = /\s+/g;

    // Store separators for further join.
    var separators = text.trim().match(regEx) || [];

    // Split the new text by regular expression.
    var newTextArray = text.split(regEx);

    // Limit the number of words.
    newTextArray.splice(limit, newTextArray.length);

    // Join the words together using stored separators.
    for (var i = 0; i < newTextArray.length; i++) {
      result += newTextArray[i] + (separators[i] || '');
    }
    return result.trim();
  }

  /**
   * Paste event higher order function for words limit.
   *
   * @since 1.5.6
   *
   * @param {number} limit Max allowed number of words.
   *
   * @return {Function} Event handler.
   */
  function pasteWords(limit) {
    return function (e) {
      e.preventDefault();
      var pastedText = getPastedText(e),
        newPosition = this.selectionStart + pastedText.length,
        newText = this.value.substring(0, this.selectionStart) + pastedText + this.value.substring(this.selectionStart);
      this.value = limitWords(newText, limit);
      this.setSelectionRange(newPosition, newPosition);
    };
  }

  /**
   * Array.from polyfill.
   *
   * @since 1.5.6
   *
   * @param {Object} el Iterator.
   *
   * @return {Object} Array.
   */
  function arrFrom(el) {
    return [].slice.call(el);
  }

  /**
   * Remove existing hint.
   *
   * @since 1.9.5.1
   *
   * @param {Object} element Element.
   */
  var removeExistingHint = function removeExistingHint(element) {
    var existingHint = element.parentNode.querySelector('.wpforms-field-limit-text');
    if (existingHint) {
      existingHint.remove();
    }
  };

  /**
   * Public functions and properties.
   *
   * @since 1.8.9
   *
   * @type {Object}
   */
  var app = {
    /**
     * Init text limit hint.
     *
     * @since 1.8.9
     *
     * @param {string} context Context selector.
     */
    initHint: function initHint(context) {
      arrFrom(document.querySelectorAll(context + ' .wpforms-limit-characters-enabled')).map(function (e) {
        // eslint-disable-line array-callback-return
        var limit = parseInt(e.dataset.textLimit, 10) || 0;
        e.value = e.value.slice(0, limit);
        var hint = createHint(e.dataset.formId, e.dataset.fieldId, renderHint(wpforms_settings.val_limit_characters, e.value.length, limit));
        var fn = checkCharacters(hint, limit);
        removeExistingHint(e);
        e.parentNode.appendChild(hint);
        e.addEventListener('keydown', fn);
        e.addEventListener('keyup', fn);
        e.addEventListener('paste', pasteText(limit));
      });
      arrFrom(document.querySelectorAll(context + ' .wpforms-limit-words-enabled')).map(function (e) {
        // eslint-disable-line array-callback-return
        var limit = parseInt(e.dataset.textLimit, 10) || 0;
        e.value = limitWords(e.value, limit);
        var hint = createHint(e.dataset.formId, e.dataset.fieldId, renderHint(wpforms_settings.val_limit_words, countWords(e.value.trim()), limit));
        var fn = checkWords(hint, limit);
        removeExistingHint(e);
        e.parentNode.appendChild(hint);
        e.addEventListener('keydown', fn);
        e.addEventListener('keyup', fn);
        e.addEventListener('paste', pasteWords(limit));
      });
    }
  };

  /**
   * DOMContentLoaded handler.
   *
   * @since 1.5.6
   */
  function ready() {
    // Expose to the world.
    window.WPFormsTextLimit = app;
    app.initHint('body');
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
  } else {
    ready();
  }
})();
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJuYW1lcyI6WyJyZW5kZXJIaW50IiwiaGludFRleHQiLCJjb3VudCIsImxpbWl0IiwicmVwbGFjZSIsImNyZWF0ZUhpbnQiLCJmb3JtSWQiLCJmaWVsZElkIiwidGV4dCIsImhpbnQiLCJkb2N1bWVudCIsImNyZWF0ZUVsZW1lbnQiLCJfdHlwZW9mIiwiY2xhc3NMaXN0IiwiYWRkIiwiaWQiLCJzZXRBdHRyaWJ1dGUiLCJ0ZXh0Q29udGVudCIsImNoZWNrQ2hhcmFjdGVycyIsImUiLCJ3aW5kb3ciLCJ3cGZvcm1zX3NldHRpbmdzIiwidmFsX2xpbWl0X2NoYXJhY3RlcnMiLCJ2YWx1ZSIsImxlbmd0aCIsImNvdW50V29yZHMiLCJzdHJpbmciLCJmb3JFYWNoIiwicGF0dGVybiIsInNwbGl0IiwiY2hlY2tXb3JkcyIsInRyaW0iLCJ3b3JkcyIsInZhbF9saW1pdF93b3JkcyIsImluZGV4T2YiLCJrZXlDb2RlIiwicHJldmVudERlZmF1bHQiLCJnZXRQYXN0ZWRUZXh0IiwiY2xpcGJvYXJkRGF0YSIsImdldERhdGEiLCJwYXN0ZVRleHQiLCJwYXN0ZWRUZXh0IiwibmV3UG9zaXRpb24iLCJzZWxlY3Rpb25TdGFydCIsIm5ld1RleHQiLCJzdWJzdHJpbmciLCJzZXRTZWxlY3Rpb25SYW5nZSIsImxpbWl0V29yZHMiLCJyZXN1bHQiLCJyZWdFeCIsInNlcGFyYXRvcnMiLCJtYXRjaCIsIm5ld1RleHRBcnJheSIsInNwbGljZSIsImkiLCJwYXN0ZVdvcmRzIiwiYXJyRnJvbSIsImVsIiwic2xpY2UiLCJjYWxsIiwicmVtb3ZlRXhpc3RpbmdIaW50IiwiZWxlbWVudCIsImV4aXN0aW5nSGludCIsInBhcmVudE5vZGUiLCJxdWVyeVNlbGVjdG9yIiwicmVtb3ZlIiwiYXBwIiwiaW5pdEhpbnQiLCJjb250ZXh0IiwicXVlcnlTZWxlY3RvckFsbCIsIm1hcCIsInBhcnNlSW50IiwiZGF0YXNldCIsInRleHRMaW1pdCIsImZuIiwiYXBwZW5kQ2hpbGQiLCJhZGRFdmVudExpc3RlbmVyIiwicmVhZHkiLCJXUEZvcm1zVGV4dExpbWl0IiwicmVhZHlTdGF0ZSJdLCJzb3VyY2VzIjpbImZha2VfOGM1ZDZlMC5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyIvKiBnbG9iYWwgd3Bmb3Jtc19zZXR0aW5ncyAqL1xuXG4oIGZ1bmN0aW9uKCkge1xuXHQvKipcblx0ICogUHJlZGVmaW5lIGhpbnQgdGV4dCB0byBkaXNwbGF5LlxuXHQgKlxuXHQgKiBAc2luY2UgMS41LjZcblx0ICogQHNpbmNlIDEuNi40IEFkZGVkIGEgbmV3IG1hY3JvcyAtIHtyZW1haW5pbmd9LlxuXHQgKlxuXHQgKiBAcGFyYW0ge3N0cmluZ30gaGludFRleHQgSGludCB0ZXh0LlxuXHQgKiBAcGFyYW0ge251bWJlcn0gY291bnQgICAgQ3VycmVudCBjb3VudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IGxpbWl0ICAgIExpbWl0IHRvLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtzdHJpbmd9IFByZWRlZmluZWQgaGludCB0ZXh0LlxuXHQgKi9cblx0ZnVuY3Rpb24gcmVuZGVySGludCggaGludFRleHQsIGNvdW50LCBsaW1pdCApIHtcblx0XHRyZXR1cm4gaGludFRleHQucmVwbGFjZSggJ3tjb3VudH0nLCBjb3VudCApLnJlcGxhY2UoICd7bGltaXR9JywgbGltaXQgKS5yZXBsYWNlKCAne3JlbWFpbmluZ30nLCBsaW1pdCAtIGNvdW50ICk7XG5cdH1cblxuXHQvKipcblx0ICogQ3JlYXRlIEhUTUxFbGVtZW50IGhpbnQgZWxlbWVudCB3aXRoIHRleHQuXG5cdCAqXG5cdCAqIEBzaW5jZSAxLjUuNlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcnxzdHJpbmd9IGZvcm1JZCAgRm9ybSBpZC5cblx0ICogQHBhcmFtIHtudW1iZXJ8c3RyaW5nfSBmaWVsZElkIEZvcm0gZmllbGQgaWQuXG5cdCAqIEBwYXJhbSB7c3RyaW5nfSAgICAgICAgdGV4dCAgICBIaW50IHRleHQuXG5cdCAqXG5cdCAqIEByZXR1cm4ge09iamVjdH0gSFRNTEVsZW1lbnQgaGludCBlbGVtZW50IHdpdGggdGV4dC5cblx0ICovXG5cdGZ1bmN0aW9uIGNyZWF0ZUhpbnQoIGZvcm1JZCwgZmllbGRJZCwgdGV4dCApIHtcblx0XHRjb25zdCBoaW50ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2RpdicgKTtcblxuXHRcdGZvcm1JZCA9IHR5cGVvZiBmb3JtSWQgPT09ICdvYmplY3QnID8gJycgOiBmb3JtSWQ7XG5cdFx0ZmllbGRJZCA9IHR5cGVvZiBmaWVsZElkID09PSAnb2JqZWN0JyA/ICcnIDogZmllbGRJZDtcblxuXHRcdGhpbnQuY2xhc3NMaXN0LmFkZCggJ3dwZm9ybXMtZmllbGQtbGltaXQtdGV4dCcgKTtcblx0XHRoaW50LmlkID0gJ3dwZm9ybXMtZmllbGQtbGltaXQtdGV4dC0nICsgZm9ybUlkICsgJy0nICsgZmllbGRJZDtcblx0XHRoaW50LnNldEF0dHJpYnV0ZSggJ2FyaWEtbGl2ZScsICdwb2xpdGUnICk7XG5cdFx0aGludC50ZXh0Q29udGVudCA9IHRleHQ7XG5cblx0XHRyZXR1cm4gaGludDtcblx0fVxuXG5cdC8qKlxuXHQgKiBLZXl1cC9LZXlkb3duIGV2ZW50IGhpZ2hlciBvcmRlciBmdW5jdGlvbiBmb3IgY2hhcmFjdGVycyBsaW1pdC5cblx0ICpcblx0ICogQHNpbmNlIDEuNS42XG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBoaW50ICBIVE1MRWxlbWVudCBoaW50IGVsZW1lbnQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBsaW1pdCBNYXggYWxsb3dlZCBudW1iZXIgb2YgY2hhcmFjdGVycy5cblx0ICpcblx0ICogQHJldHVybiB7RnVuY3Rpb259IEhhbmRsZXIgZnVuY3Rpb24uXG5cdCAqL1xuXHRmdW5jdGlvbiBjaGVja0NoYXJhY3RlcnMoIGhpbnQsIGxpbWl0ICkge1xuXHRcdC8vIG5vaW5zcGVjdGlvbiBKU1VudXNlZExvY2FsU3ltYm9sc1xuXHRcdHJldHVybiBmdW5jdGlvbiggZSApIHsgLy8gZXNsaW50LWRpc2FibGUtbGluZSBuby11bnVzZWQtdmFyc1xuXHRcdFx0aGludC50ZXh0Q29udGVudCA9IHJlbmRlckhpbnQoXG5cdFx0XHRcdHdpbmRvdy53cGZvcm1zX3NldHRpbmdzLnZhbF9saW1pdF9jaGFyYWN0ZXJzLFxuXHRcdFx0XHR0aGlzLnZhbHVlLmxlbmd0aCxcblx0XHRcdFx0bGltaXRcblx0XHRcdCk7XG5cdFx0fTtcblx0fVxuXG5cdC8qKlxuXHQgKiBDb3VudCB3b3JkcyBpbiB0aGUgc3RyaW5nLlxuXHQgKlxuXHQgKiBAc2luY2UgMS42LjJcblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHN0cmluZyBTdHJpbmcgdmFsdWUuXG5cdCAqXG5cdCAqIEByZXR1cm4ge251bWJlcn0gV29yZHMgY291bnQuXG5cdCAqL1xuXHRmdW5jdGlvbiBjb3VudFdvcmRzKCBzdHJpbmcgKSB7XG5cdFx0aWYgKCB0eXBlb2Ygc3RyaW5nICE9PSAnc3RyaW5nJyApIHtcblx0XHRcdHJldHVybiAwO1xuXHRcdH1cblxuXHRcdGlmICggISBzdHJpbmcubGVuZ3RoICkge1xuXHRcdFx0cmV0dXJuIDA7XG5cdFx0fVxuXG5cdFx0W1xuXHRcdFx0LyhbQS1aXSspLChbQS1aXSspL2dpLFxuXHRcdFx0LyhbMC05XSspLChbQS1aXSspL2dpLFxuXHRcdFx0LyhbQS1aXSspLChbMC05XSspL2dpLFxuXHRcdF0uZm9yRWFjaCggZnVuY3Rpb24oIHBhdHRlcm4gKSB7XG5cdFx0XHRzdHJpbmcgPSBzdHJpbmcucmVwbGFjZSggcGF0dGVybiwgJyQxLCAkMicgKTtcblx0XHR9ICk7XG5cblx0XHRyZXR1cm4gc3RyaW5nLnNwbGl0KCAvXFxzKy8gKS5sZW5ndGg7XG5cdH1cblxuXHQvKipcblx0ICogS2V5dXAvS2V5ZG93biBldmVudCBoaWdoZXIgb3JkZXIgZnVuY3Rpb24gZm9yIHdvcmRzIGxpbWl0LlxuXHQgKlxuXHQgKiBAc2luY2UgMS41LjZcblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3R9IGhpbnQgIEhUTUxFbGVtZW50IGhpbnQgZWxlbWVudC5cblx0ICogQHBhcmFtIHtudW1iZXJ9IGxpbWl0IE1heCBhbGxvd2VkIG51bWJlciBvZiBjaGFyYWN0ZXJzLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtGdW5jdGlvbn0gSGFuZGxlciBmdW5jdGlvbi5cblx0ICovXG5cdGZ1bmN0aW9uIGNoZWNrV29yZHMoIGhpbnQsIGxpbWl0ICkge1xuXHRcdHJldHVybiBmdW5jdGlvbiggZSApIHtcblx0XHRcdGNvbnN0IHZhbHVlID0gdGhpcy52YWx1ZS50cmltKCksXG5cdFx0XHRcdHdvcmRzID0gY291bnRXb3JkcyggdmFsdWUgKTtcblxuXHRcdFx0aGludC50ZXh0Q29udGVudCA9IHJlbmRlckhpbnQoXG5cdFx0XHRcdHdpbmRvdy53cGZvcm1zX3NldHRpbmdzLnZhbF9saW1pdF93b3Jkcyxcblx0XHRcdFx0d29yZHMsXG5cdFx0XHRcdGxpbWl0XG5cdFx0XHQpO1xuXG5cdFx0XHQvLyBXZSBzaG91bGQgcHJldmVudCB0aGUga2V5czogRW50ZXIsIFNwYWNlLCBDb21tYS5cblx0XHRcdGlmICggWyAxMywgMzIsIDE4OCBdLmluZGV4T2YoIGUua2V5Q29kZSApID4gLTEgJiYgd29yZHMgPj0gbGltaXQgKSB7XG5cdFx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblx0XHRcdH1cblx0XHR9O1xuXHR9XG5cblx0LyoqXG5cdCAqIEdldCBwYXNzZWQgdGV4dCBmcm9tIHRoZSBjbGlwYm9hcmQuXG5cdCAqXG5cdCAqIEBzaW5jZSAxLjUuNlxuXHQgKlxuXHQgKiBAcGFyYW0ge0NsaXBib2FyZEV2ZW50fSBlIENsaXBib2FyZCBldmVudC5cblx0ICpcblx0ICogQHJldHVybiB7c3RyaW5nfSBUZXh0IGZyb20gY2xpcGJvYXJkLlxuXHQgKi9cblx0ZnVuY3Rpb24gZ2V0UGFzdGVkVGV4dCggZSApIHtcblx0XHRpZiAoIHdpbmRvdy5jbGlwYm9hcmREYXRhICYmIHdpbmRvdy5jbGlwYm9hcmREYXRhLmdldERhdGEgKSB7IC8vIElFXG5cdFx0XHRyZXR1cm4gd2luZG93LmNsaXBib2FyZERhdGEuZ2V0RGF0YSggJ1RleHQnICk7XG5cdFx0fSBlbHNlIGlmICggZS5jbGlwYm9hcmREYXRhICYmIGUuY2xpcGJvYXJkRGF0YS5nZXREYXRhICkge1xuXHRcdFx0cmV0dXJuIGUuY2xpcGJvYXJkRGF0YS5nZXREYXRhKCAndGV4dC9wbGFpbicgKTtcblx0XHR9XG5cblx0XHRyZXR1cm4gJyc7XG5cdH1cblxuXHQvKipcblx0ICogUGFzdGUgZXZlbnQgaGlnaGVyIG9yZGVyIGZ1bmN0aW9uIGZvciBjaGFyYWN0ZXIgbGltaXQuXG5cdCAqXG5cdCAqIEBzaW5jZSAxLjYuNy4xXG5cdCAqXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBsaW1pdCBNYXggYWxsb3dlZCBudW1iZXIgb2YgY2hhcmFjdGVycy5cblx0ICpcblx0ICogQHJldHVybiB7RnVuY3Rpb259IEV2ZW50IGhhbmRsZXIuXG5cdCAqL1xuXHRmdW5jdGlvbiBwYXN0ZVRleHQoIGxpbWl0ICkge1xuXHRcdHJldHVybiBmdW5jdGlvbiggZSApIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0Y29uc3QgcGFzdGVkVGV4dCA9IGdldFBhc3RlZFRleHQoIGUgKSxcblx0XHRcdFx0bmV3UG9zaXRpb24gPSB0aGlzLnNlbGVjdGlvblN0YXJ0ICsgcGFzdGVkVGV4dC5sZW5ndGgsXG5cdFx0XHRcdG5ld1RleHQgPSB0aGlzLnZhbHVlLnN1YnN0cmluZyggMCwgdGhpcy5zZWxlY3Rpb25TdGFydCApICsgcGFzdGVkVGV4dCArIHRoaXMudmFsdWUuc3Vic3RyaW5nKCB0aGlzLnNlbGVjdGlvblN0YXJ0ICk7XG5cblx0XHRcdHRoaXMudmFsdWUgPSBuZXdUZXh0LnN1YnN0cmluZyggMCwgbGltaXQgKTtcblx0XHRcdHRoaXMuc2V0U2VsZWN0aW9uUmFuZ2UoIG5ld1Bvc2l0aW9uLCBuZXdQb3NpdGlvbiApO1xuXHRcdH07XG5cdH1cblxuXHQvKipcblx0ICogTGltaXQgc3RyaW5nIGxlbmd0aCB0byBhIGNlcnRhaW4gbnVtYmVyIG9mIHdvcmRzLCBwcmVzZXJ2aW5nIGxpbmUgYnJlYWtzLlxuXHQgKlxuXHQgKiBAc2luY2UgMS42Ljhcblx0ICpcblx0ICogQHBhcmFtIHtzdHJpbmd9IHRleHQgIFRleHQuXG5cdCAqIEBwYXJhbSB7bnVtYmVyfSBsaW1pdCBNYXggYWxsb3dlZCBudW1iZXIgb2Ygd29yZHMuXG5cdCAqXG5cdCAqIEByZXR1cm4ge3N0cmluZ30gVGV4dCB3aXRoIHRoZSBsaW1pdGVkIG51bWJlciBvZiB3b3Jkcy5cblx0ICovXG5cdGZ1bmN0aW9uIGxpbWl0V29yZHMoIHRleHQsIGxpbWl0ICkge1xuXHRcdGxldCByZXN1bHQgPSAnJztcblxuXHRcdC8vIFJlZ3VsYXIgZXhwcmVzc2lvbiBwYXR0ZXJuOiBtYXRjaCBhbnkgc3BhY2UgY2hhcmFjdGVyLlxuXHRcdGNvbnN0IHJlZ0V4ID0gL1xccysvZztcblxuXHRcdC8vIFN0b3JlIHNlcGFyYXRvcnMgZm9yIGZ1cnRoZXIgam9pbi5cblx0XHRjb25zdCBzZXBhcmF0b3JzID0gdGV4dC50cmltKCkubWF0Y2goIHJlZ0V4ICkgfHwgW107XG5cblx0XHQvLyBTcGxpdCB0aGUgbmV3IHRleHQgYnkgcmVndWxhciBleHByZXNzaW9uLlxuXHRcdGNvbnN0IG5ld1RleHRBcnJheSA9IHRleHQuc3BsaXQoIHJlZ0V4ICk7XG5cblx0XHQvLyBMaW1pdCB0aGUgbnVtYmVyIG9mIHdvcmRzLlxuXHRcdG5ld1RleHRBcnJheS5zcGxpY2UoIGxpbWl0LCBuZXdUZXh0QXJyYXkubGVuZ3RoICk7XG5cblx0XHQvLyBKb2luIHRoZSB3b3JkcyB0b2dldGhlciB1c2luZyBzdG9yZWQgc2VwYXJhdG9ycy5cblx0XHRmb3IgKCBsZXQgaSA9IDA7IGkgPCBuZXdUZXh0QXJyYXkubGVuZ3RoOyBpKysgKSB7XG5cdFx0XHRyZXN1bHQgKz0gbmV3VGV4dEFycmF5WyBpIF0gKyAoIHNlcGFyYXRvcnNbIGkgXSB8fCAnJyApO1xuXHRcdH1cblxuXHRcdHJldHVybiByZXN1bHQudHJpbSgpO1xuXHR9XG5cblx0LyoqXG5cdCAqIFBhc3RlIGV2ZW50IGhpZ2hlciBvcmRlciBmdW5jdGlvbiBmb3Igd29yZHMgbGltaXQuXG5cdCAqXG5cdCAqIEBzaW5jZSAxLjUuNlxuXHQgKlxuXHQgKiBAcGFyYW0ge251bWJlcn0gbGltaXQgTWF4IGFsbG93ZWQgbnVtYmVyIG9mIHdvcmRzLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtGdW5jdGlvbn0gRXZlbnQgaGFuZGxlci5cblx0ICovXG5cdGZ1bmN0aW9uIHBhc3RlV29yZHMoIGxpbWl0ICkge1xuXHRcdHJldHVybiBmdW5jdGlvbiggZSApIHtcblx0XHRcdGUucHJldmVudERlZmF1bHQoKTtcblxuXHRcdFx0Y29uc3QgcGFzdGVkVGV4dCA9IGdldFBhc3RlZFRleHQoIGUgKSxcblx0XHRcdFx0bmV3UG9zaXRpb24gPSB0aGlzLnNlbGVjdGlvblN0YXJ0ICsgcGFzdGVkVGV4dC5sZW5ndGgsXG5cdFx0XHRcdG5ld1RleHQgPSB0aGlzLnZhbHVlLnN1YnN0cmluZyggMCwgdGhpcy5zZWxlY3Rpb25TdGFydCApICsgcGFzdGVkVGV4dCArIHRoaXMudmFsdWUuc3Vic3RyaW5nKCB0aGlzLnNlbGVjdGlvblN0YXJ0ICk7XG5cblx0XHRcdHRoaXMudmFsdWUgPSBsaW1pdFdvcmRzKCBuZXdUZXh0LCBsaW1pdCApO1xuXHRcdFx0dGhpcy5zZXRTZWxlY3Rpb25SYW5nZSggbmV3UG9zaXRpb24sIG5ld1Bvc2l0aW9uICk7XG5cdFx0fTtcblx0fVxuXG5cdC8qKlxuXHQgKiBBcnJheS5mcm9tIHBvbHlmaWxsLlxuXHQgKlxuXHQgKiBAc2luY2UgMS41LjZcblx0ICpcblx0ICogQHBhcmFtIHtPYmplY3R9IGVsIEl0ZXJhdG9yLlxuXHQgKlxuXHQgKiBAcmV0dXJuIHtPYmplY3R9IEFycmF5LlxuXHQgKi9cblx0ZnVuY3Rpb24gYXJyRnJvbSggZWwgKSB7XG5cdFx0cmV0dXJuIFtdLnNsaWNlLmNhbGwoIGVsICk7XG5cdH1cblxuXHQvKipcblx0ICogUmVtb3ZlIGV4aXN0aW5nIGhpbnQuXG5cdCAqXG5cdCAqIEBzaW5jZSAxLjkuNS4xXG5cdCAqXG5cdCAqIEBwYXJhbSB7T2JqZWN0fSBlbGVtZW50IEVsZW1lbnQuXG5cdCAqL1xuXHRjb25zdCByZW1vdmVFeGlzdGluZ0hpbnQgPSAoIGVsZW1lbnQgKSA9PiB7XG5cdFx0Y29uc3QgZXhpc3RpbmdIaW50ID0gZWxlbWVudC5wYXJlbnROb2RlLnF1ZXJ5U2VsZWN0b3IoICcud3Bmb3Jtcy1maWVsZC1saW1pdC10ZXh0JyApO1xuXHRcdGlmICggZXhpc3RpbmdIaW50ICkge1xuXHRcdFx0ZXhpc3RpbmdIaW50LnJlbW92ZSgpO1xuXHRcdH1cblx0fTtcblxuXHQvKipcblx0ICogUHVibGljIGZ1bmN0aW9ucyBhbmQgcHJvcGVydGllcy5cblx0ICpcblx0ICogQHNpbmNlIDEuOC45XG5cdCAqXG5cdCAqIEB0eXBlIHtPYmplY3R9XG5cdCAqL1xuXHRjb25zdCBhcHAgPSB7XG5cdFx0LyoqXG5cdFx0ICogSW5pdCB0ZXh0IGxpbWl0IGhpbnQuXG5cdFx0ICpcblx0XHQgKiBAc2luY2UgMS44Ljlcblx0XHQgKlxuXHRcdCAqIEBwYXJhbSB7c3RyaW5nfSBjb250ZXh0IENvbnRleHQgc2VsZWN0b3IuXG5cdFx0ICovXG5cdFx0aW5pdEhpbnQoIGNvbnRleHQgKSB7XG5cdFx0XHRhcnJGcm9tKCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCBjb250ZXh0ICsgJyAud3Bmb3Jtcy1saW1pdC1jaGFyYWN0ZXJzLWVuYWJsZWQnICkgKVxuXHRcdFx0XHQubWFwKFxuXHRcdFx0XHRcdGZ1bmN0aW9uKCBlICkgeyAvLyBlc2xpbnQtZGlzYWJsZS1saW5lIGFycmF5LWNhbGxiYWNrLXJldHVyblxuXHRcdFx0XHRcdFx0Y29uc3QgbGltaXQgPSBwYXJzZUludCggZS5kYXRhc2V0LnRleHRMaW1pdCwgMTAgKSB8fCAwO1xuXG5cdFx0XHRcdFx0XHRlLnZhbHVlID0gZS52YWx1ZS5zbGljZSggMCwgbGltaXQgKTtcblxuXHRcdFx0XHRcdFx0Y29uc3QgaGludCA9IGNyZWF0ZUhpbnQoXG5cdFx0XHRcdFx0XHRcdGUuZGF0YXNldC5mb3JtSWQsXG5cdFx0XHRcdFx0XHRcdGUuZGF0YXNldC5maWVsZElkLFxuXHRcdFx0XHRcdFx0XHRyZW5kZXJIaW50KFxuXHRcdFx0XHRcdFx0XHRcdHdwZm9ybXNfc2V0dGluZ3MudmFsX2xpbWl0X2NoYXJhY3RlcnMsXG5cdFx0XHRcdFx0XHRcdFx0ZS52YWx1ZS5sZW5ndGgsXG5cdFx0XHRcdFx0XHRcdFx0bGltaXRcblx0XHRcdFx0XHRcdFx0KVxuXHRcdFx0XHRcdFx0KTtcblxuXHRcdFx0XHRcdFx0Y29uc3QgZm4gPSBjaGVja0NoYXJhY3RlcnMoIGhpbnQsIGxpbWl0ICk7XG5cblx0XHRcdFx0XHRcdHJlbW92ZUV4aXN0aW5nSGludCggZSApO1xuXG5cdFx0XHRcdFx0XHRlLnBhcmVudE5vZGUuYXBwZW5kQ2hpbGQoIGhpbnQgKTtcblx0XHRcdFx0XHRcdGUuYWRkRXZlbnRMaXN0ZW5lciggJ2tleWRvd24nLCBmbiApO1xuXHRcdFx0XHRcdFx0ZS5hZGRFdmVudExpc3RlbmVyKCAna2V5dXAnLCBmbiApO1xuXHRcdFx0XHRcdFx0ZS5hZGRFdmVudExpc3RlbmVyKCAncGFzdGUnLCBwYXN0ZVRleHQoIGxpbWl0ICkgKTtcblx0XHRcdFx0XHR9XG5cdFx0XHRcdCk7XG5cblx0XHRcdGFyckZyb20oIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoIGNvbnRleHQgKyAnIC53cGZvcm1zLWxpbWl0LXdvcmRzLWVuYWJsZWQnICkgKVxuXHRcdFx0XHQubWFwKFxuXHRcdFx0XHRcdGZ1bmN0aW9uKCBlICkgeyAvLyBlc2xpbnQtZGlzYWJsZS1saW5lIGFycmF5LWNhbGxiYWNrLXJldHVyblxuXHRcdFx0XHRcdFx0Y29uc3QgbGltaXQgPSBwYXJzZUludCggZS5kYXRhc2V0LnRleHRMaW1pdCwgMTAgKSB8fCAwO1xuXG5cdFx0XHRcdFx0XHRlLnZhbHVlID0gbGltaXRXb3JkcyggZS52YWx1ZSwgbGltaXQgKTtcblxuXHRcdFx0XHRcdFx0Y29uc3QgaGludCA9IGNyZWF0ZUhpbnQoXG5cdFx0XHRcdFx0XHRcdGUuZGF0YXNldC5mb3JtSWQsXG5cdFx0XHRcdFx0XHRcdGUuZGF0YXNldC5maWVsZElkLFxuXHRcdFx0XHRcdFx0XHRyZW5kZXJIaW50KFxuXHRcdFx0XHRcdFx0XHRcdHdwZm9ybXNfc2V0dGluZ3MudmFsX2xpbWl0X3dvcmRzLFxuXHRcdFx0XHRcdFx0XHRcdGNvdW50V29yZHMoIGUudmFsdWUudHJpbSgpICksXG5cdFx0XHRcdFx0XHRcdFx0bGltaXRcblx0XHRcdFx0XHRcdFx0KVxuXHRcdFx0XHRcdFx0KTtcblxuXHRcdFx0XHRcdFx0Y29uc3QgZm4gPSBjaGVja1dvcmRzKCBoaW50LCBsaW1pdCApO1xuXG5cdFx0XHRcdFx0XHRyZW1vdmVFeGlzdGluZ0hpbnQoIGUgKTtcblxuXHRcdFx0XHRcdFx0ZS5wYXJlbnROb2RlLmFwcGVuZENoaWxkKCBoaW50ICk7XG5cblx0XHRcdFx0XHRcdGUuYWRkRXZlbnRMaXN0ZW5lciggJ2tleWRvd24nLCBmbiApO1xuXHRcdFx0XHRcdFx0ZS5hZGRFdmVudExpc3RlbmVyKCAna2V5dXAnLCBmbiApO1xuXHRcdFx0XHRcdFx0ZS5hZGRFdmVudExpc3RlbmVyKCAncGFzdGUnLCBwYXN0ZVdvcmRzKCBsaW1pdCApICk7XG5cdFx0XHRcdFx0fVxuXHRcdFx0XHQpO1xuXHRcdH0sXG5cdH07XG5cblx0LyoqXG5cdCAqIERPTUNvbnRlbnRMb2FkZWQgaGFuZGxlci5cblx0ICpcblx0ICogQHNpbmNlIDEuNS42XG5cdCAqL1xuXHRmdW5jdGlvbiByZWFkeSgpIHtcblx0XHQvLyBFeHBvc2UgdG8gdGhlIHdvcmxkLlxuXHRcdHdpbmRvdy5XUEZvcm1zVGV4dExpbWl0ID0gYXBwO1xuXG5cdFx0YXBwLmluaXRIaW50KCAnYm9keScgKTtcblx0fVxuXG5cdGlmICggZG9jdW1lbnQucmVhZHlTdGF0ZSA9PT0gJ2xvYWRpbmcnICkge1xuXHRcdGRvY3VtZW50LmFkZEV2ZW50TGlzdGVuZXIoICdET01Db250ZW50TG9hZGVkJywgcmVhZHkgKTtcblx0fSBlbHNlIHtcblx0XHRyZWFkeSgpO1xuXHR9XG59KCkgKTtcbiJdLCJtYXBwaW5ncyI6Ijs7O0FBQUE7O0FBRUUsYUFBVztFQUNaO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtFQUNDLFNBQVNBLFVBQVVBLENBQUVDLFFBQVEsRUFBRUMsS0FBSyxFQUFFQyxLQUFLLEVBQUc7SUFDN0MsT0FBT0YsUUFBUSxDQUFDRyxPQUFPLENBQUUsU0FBUyxFQUFFRixLQUFNLENBQUMsQ0FBQ0UsT0FBTyxDQUFFLFNBQVMsRUFBRUQsS0FBTSxDQUFDLENBQUNDLE9BQU8sQ0FBRSxhQUFhLEVBQUVELEtBQUssR0FBR0QsS0FBTSxDQUFDO0VBQ2hIOztFQUVBO0FBQ0Q7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7RUFDQyxTQUFTRyxVQUFVQSxDQUFFQyxNQUFNLEVBQUVDLE9BQU8sRUFBRUMsSUFBSSxFQUFHO0lBQzVDLElBQU1DLElBQUksR0FBR0MsUUFBUSxDQUFDQyxhQUFhLENBQUUsS0FBTSxDQUFDO0lBRTVDTCxNQUFNLEdBQUdNLE9BQUEsQ0FBT04sTUFBTSxNQUFLLFFBQVEsR0FBRyxFQUFFLEdBQUdBLE1BQU07SUFDakRDLE9BQU8sR0FBR0ssT0FBQSxDQUFPTCxPQUFPLE1BQUssUUFBUSxHQUFHLEVBQUUsR0FBR0EsT0FBTztJQUVwREUsSUFBSSxDQUFDSSxTQUFTLENBQUNDLEdBQUcsQ0FBRSwwQkFBMkIsQ0FBQztJQUNoREwsSUFBSSxDQUFDTSxFQUFFLEdBQUcsMkJBQTJCLEdBQUdULE1BQU0sR0FBRyxHQUFHLEdBQUdDLE9BQU87SUFDOURFLElBQUksQ0FBQ08sWUFBWSxDQUFFLFdBQVcsRUFBRSxRQUFTLENBQUM7SUFDMUNQLElBQUksQ0FBQ1EsV0FBVyxHQUFHVCxJQUFJO0lBRXZCLE9BQU9DLElBQUk7RUFDWjs7RUFF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
},{}]},{},[1])