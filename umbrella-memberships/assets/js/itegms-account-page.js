/**
 * JS for the account page.
 *
 * @author      Iron Bound Designs
 * @since       1.0
 * @copyright   2015 (c) Iron Bound Designs.
 * @license     GPLv2
 */

jQuery(document).ready(function ($) {

	$(".itegms-member-form h4").click(function (e) {
		e.preventDefault();

		var members_list = $("#itegms-members-list");

		if (members_list.is(':hidden')) {
			members_list.show();
			$(this).addClass('itegms-list-open');
		} else {
			members_list.hide();
			$(this).removeClass('itegms-list-open');
		}
	});

	$(".itegms-remove-member").click(function (e) {

		e.preventDefault();

		var i = $(this).data('id');

		$("#itegms-member-" + i + "-email").val('').prop('readonly', false);
		$("#itegms-member-" + i + "-name").val('').prop('readonly', false);
	});

	$(".itegms-pagination li button").click(function (e) {

		e.preventDefault();

		$(".itegms-page-current").removeClass('itegms-page-current').addClass('itegms-page-hidden');

		var page = $(this).data('page');

		$(".itegms-page-" + page).removeClass('itegms-page-hidden').addClass('itegms-page-current');

		$(".itegms-pagination li button[disabled]").prop('disabled', false);
		$(this).prop('disabled', true);
	});

});