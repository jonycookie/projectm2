/**
 * jQuery calendar plugin
 *
 * Copyright (c) 2009 Snitko Roman
 * Dual licensed under the MIT (MIT-LICENSE.txt)
 * and GPL (GPL-LICENSE.txt) licenses.
 *
 * @author 	Roman Snitko snowcore.net@gmail.com
 * @link http://snowcore.net/
 * @version 0.1.11
 */
;(function($){
    $.fn.calendar = function(options) {
        var opts = $.extend({}, $.fn.calendar.defaults, options);
        return this.each(function() {
            $this = $(this);
            var o = $.meta ? $.extend({}, opts, $this.data()) : opts;
            $.fn.calendar.draw(o);
        });
    };
    
    /**
     * Get calendar's header (day names)
     * @param {Object} opts
     */
    $.fn.calendar.getHead = function(opts) {
	    var html = [];
	    for (var i = 0; i < opts.days.length; i++) {
	        html.push('<th>' + opts.days[i] + '</th>');
	    }
	    return html.join('');
    };
    
    /**
     * Main function for drawing calendar
     * @param {Object} opts
     */
    $.fn.calendar.draw = function(opts) {
        var o = $.extend({}, this.defaults, opts);
        
        month = parseInt(o.month, 10);
        year = parseInt(o.year, 10);

        var today = new Date();
        var srcDate = new Date();
        if (!isNaN(year)) {
            srcDate.setDate(1);
            srcDate.setFullYear(year);
        }
        if (!isNaN(month)) {
            srcDate.setDate(1);
            srcDate.setMonth(month);
        }
        var curDate = srcDate.getDate();
        var curMonth = srcDate.getMonth();
        var curYear = srcDate.getFullYear();

        var dates = [];
        var dayCount = new Date(curYear, curMonth + 1, 0).getDate();
        for (var i = 1; i <= dayCount; i++) {
            var tmpDate = new Date(curYear, curMonth, i);
            if (tmpDate.getMonth() == curMonth && tmpDate.getFullYear() == curYear) {
                dates.push(tmpDate);
            }
        }

        var table = $('<table cellspacing="1" class="table"></table>');
        var str = '<tbody><tr>' + this.getHead(o) + '</tr>', cl = '';
        var line = [];
        for (var j = 0; j < dates.length; j++) {
            var day = dates[j].getDay();
            var month = dates[j].getMonth();
            var year = dates[j].getFullYear();
            
            var date = dates[j].getDate();
            var rel = _formatLink(o.dateFormat, dates[j]);
            cl = '';
            if (date == today.getDate() && curMonth == today.getMonth() && curYear == today.getFullYear()) {
                cl = ' class="curr"';
            } else if (day == 6 || day == 0) {
                cl = ' class="weekend"';
            }
            var href = '#';
            if (o.linkFormat != null && o.linkFormat != undefined) {
                href = _formatLink(o.linkFormat, dates[j]);
            }
            line.push('<td' + cl + '><a href="' + href + '" rel="' + rel + '">' + date + '</a></td>');
            if (dates[j].getDay() == 0) {
                if (line.length < 7) {
                    var ln = line.length;
                    var pad = [];
                    for (var k = 0; k < (7 - ln); k++) {
                        pad.push('<td>&nbsp;</td>');
                    }
                    line = pad.concat(line);
                }
                str += '<tr>' + line.join('') + '</tr>';
                line = [];
            } else if (j == (dates.length - 1)) {
                str += '<tr>' + line.join('') + '</tr>';
            }
        }
        str += '</tbody>';
        table.html(str);
        if (typeof o.onSelect == 'function') {
            table.find('a').click(function(){
                return o.onSelect($(this).attr('rel'));
            });
        }
        var y = '';
        if (o.showYear == true) {
            y = ' ' + curYear + ' ';
        }
        $this[0].innerHTML = '<span class="state" style="display:none;">' + curMonth + '.' + curYear + '</span><div class="monthName"><a href="#" class="next">' + o.nextArrow + '</a><a href="#" class="prev">' + o.prevArrow + '</a> ' + o.months[curMonth] + y + ' </div>';
        $this.append(table)
             .find('.monthName').width($this.find('.table').width());
        this.next($this.find('.next'), o);
        this.prev($this.find('.prev'), o);
    };
    
    /**
     * Format link
     * @param {String} format
     * @param {Object} objDate
     */
    function _formatLink(format, objDate) {
        var date = objDate.getDate();
        var month = objDate.getMonth() + 1;
        var year = objDate.getFullYear();
        if (format.indexOf('{%dd}') != -1) {
            date = _formatNum(date);
        }
        if (format.indexOf('{%mm}') != -1) {
            month = _formatNum(month);
        }
        if (format.indexOf('{%yy}') != -1) {
            year = year.toString().slice(2);
        }
        var link = format.replace(/{%d(d)?}/, date).replace(/{%m(m)?}/, month).replace(/{%yy(yy)?}/, year);
        return link;
    };
    
    /**
     * Format date for link's 'rel' attribute
     * @param {String} format
     * @param {Object} objDate
     */
    function _formatDate(format, objDate) {
        var date = objDate.getDate();
        var month = objDate.getMonth();
        var year = objDate.getFullYear();
        if (format.indexOf('{%dd}') != -1) {
            date = _formatNum(date);
        }
        if (format.indexOf('{%mm}') != -1) {
            month = _formatNum(month);
        }
        if (format.indexOf('{%yy}') != -1) {
            year = year.toString().slice(2);
        }
        month++;
        var link = format.replace(/{%d(d)?}/, date).replace(/{%m(m)?}/, month).replace(/{%yy(yy)?}/, year);
        return link;
    };
    
    /**
     * Show next month
     */
    $.fn.calendar.next = function(button, o) {
        this.change(button, 1, o);
    };
    
    /**
     * Show previous month
     * @param {Object} button
     */
    $.fn.calendar.prev = function(button, o) {
        this.change(button, -1, o);
    };
    
    /**
     * Switch to another month
     * @param {Object} button
     * @param {Integer} monthDelta
     */
    $.fn.calendar.change = function(button, monthDelta, o) {
        var opts = $.extend({}, $.fn.calendar.defaults, o);
        var _self = this;
        var parent = button.parent('.monthName').parent();
        button.click(function(){
            var state = _self.getState(parent);
            var d = new Date(state[1], state[0] + monthDelta, 1);
            opts.month = d.getMonth();
            opts.year  = d.getFullYear();
            parent.calendar(opts);
            return false;
        });
    };
    
    /**
     * Get current calendar's state (month and year)
     */
    $.fn.calendar.getState = function(div) {
        var st = div.find('.state')[0].innerHTML.split('.');
        return [parseInt(st[0], 10), parseInt(st[1], 10)];
    };
    
    /**
     * Format number (pad with zero)
     * @param {Integer} num
     */
    function _formatNum(num) {
        num = parseInt(num, 10);
        if (num < 10) {
            return '0' + num;
        }
        return num;
    };
    
    /**
     * Default options
     */
    $.fn.calendar.defaults = {
        /**
         * Names of week's days
         */
        days: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],
        
        /**
         * Month names
         */
        months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
                 'September', 'October', 'November', 'December'],
        
        /**
         * Link to be assigned for each link in cell:
         *   e.g.: http://snowcore.net/events/{%dd}-{%mm}-{%yyyy}
         * 
         * Possible values:
         *   {%dd} - date with leading zero
         *   {%d}  - date without leading zero
         *   {%mm} - month with leading zero
         *   {%m}  - month without leading zero
         *   {%yy} - yar (two digits)
         *   {%yyyy}  - full year (for digits)
         */
        linkFormat: null,
        
        /**
         * Format for date in 'rel' attribute (onSelect callback function retrieves this value)
         * Formatting options are the same as for 'linkFormat' option
         * Default: mm.dd.yyyy ('{%dd}.{%mm}.{%yyyy}')
         */
        dateFormat: '{%dd}.{%mm}.{%yyyy}',
        
        /**
         * Callback function, fires when user click on the cell
         * Function retrives one parameter - date
         * (in format that has been assigned by 'dateFormat' option):
         */
        onSelect: null,
        
        /**
         * When set to true, full year is displaying in the calendar caption
         */
        showYear: false,
        
        /**
         * Prev/Next arrows (you can also use &larr; and &rarr;)
         */
        prevArrow: '&laquo;',
        nextArrow: '&raquo;'
    };
})(jQuery);