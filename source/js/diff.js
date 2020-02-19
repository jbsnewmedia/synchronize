/**
 * @author Juergen Schwind
 * @copyright Copyright (c), JBS New Media GmbH
 * @package JBS New Media - Synchronize
 * @link https://jbs-newmedia.de
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License
 *
 */

$(function() {
	$('.viewdiff_content ins').addClass('jumper');
	$('.viewdiff_content del').addClass('jumper');
	
	$("body").keydown(function(e) {
		if ((e.keyCode == 37)||(e.keyCode == 65)) {
			clickPrev();
		} else if ((e.keyCode == 39)||(e.keyCode == 68)) {
			clickNext();
		}
	});
});

function clickNext() {
	if ($('.viewdiff_content .jumper.current').hasClass('current')!==true) {
		$('.viewdiff_content .jumper:first').addClass('current');
	} else {
	  $('.viewdiff_content .jumper.current').nextAll('.viewdiff_content .jumper:first').addClass('current');
	  $('.viewdiff_content .jumper.current:first').removeClass('current');
	}
	animateCurrent();
}

function clickPrev() {
	if ($('.viewdiff_content .jumper.current').hasClass('current')!==true) {
		$('.viewdiff_content .jumper:last').addClass('current');
	} else {
	  $('.viewdiff_content .jumper.current').prevAll('.viewdiff_content .jumper:first').addClass('current');
	  $('.viewdiff_content .jumper.current:last').removeClass('current');
	}
	animateCurrent();
}

function animateCurrent() {
	$('html, body').animate({scrollTop:$('.viewdiff_content .current').offset().top -30}, 500);
}