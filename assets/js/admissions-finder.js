/**
 * Admissions — Program Finder
 * Cinsiyet + sınıf seçimine göre program kartını anlık günceller.
 */
( function () {
	'use strict';

	var DATA = window.AdmissionsData || {};

	/**
	 * Analytics olayı gönder (GTM dataLayer + gtag + özel DOM olayı).
	 */
	function track( event, payload ) {
		payload = payload || {};
		try {
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push( Object.assign( { event: event }, payload ) );
		} catch ( e ) {}
		if ( typeof window.gtag === 'function' ) {
			window.gtag( 'event', event, payload );
		}
		try {
			document.dispatchEvent( new CustomEvent( 'admissions:' + event, { detail: payload } ) );
		} catch ( e ) {}
	}

	/**
	 * Find the matching rule for a gender + grade (first match wins).
	 */
	function matchRule( gender, grade ) {
		var rules = DATA.rules || [];
		grade = parseInt( grade, 10 );
		for ( var i = 0; i < rules.length; i++ ) {
			var r = rules[ i ];
			if ( r.gender !== gender ) {
				continue;
			}
			if ( grade >= parseInt( r.min, 10 ) && grade <= parseInt( r.max, 10 ) ) {
				return r;
			}
		}
		return null;
	}

	/**
	 * URL parametrelerini güncelle (sayfa yenilemeden).
	 */
	function syncUrl( gender, grade ) {
		if ( ! window.history || ! window.history.replaceState ) {
			return;
		}
		var url = new URL( window.location.href );
		if ( gender ) {
			url.searchParams.set( 'gender', gender );
		} else {
			url.searchParams.delete( 'gender' );
		}
		if ( grade !== null && grade !== '' && typeof grade !== 'undefined' ) {
			url.searchParams.set( 'grade', grade );
		} else {
			url.searchParams.delete( 'grade' );
		}
		window.history.replaceState( {}, '', url.toString() );
	}

	/**
	 * Basit metin kaçışı.
	 */
	function esc( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str == null ? '' : String( str );
		return div.innerHTML;
	}

	/**
	 * CTA butonu HTML'i.
	 */
	function ctaButton( href, label, isSecondary ) {
		if ( ! href ) {
			return '';
		}
		var cls = 'admissions-finder__cta' + ( isSecondary ? ' admissions-finder__cta--secondary' : '' );
		var arrow = '<svg class="admissions-finder__cta-arrow" viewBox="0 0 16 16" width="15" height="15" aria-hidden="true" focusable="false"><path d="M3 8h9M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		return '<a class="' + cls + '" href="' + esc( href ) + '"><span>' + esc( label ) + '</span>' + arrow + '</a>';
	}

	/**
	 * Kartı yumuşak bir geçişle göster.
	 */
	function reveal( el ) {
		el.hidden = false;
		el.classList.remove( 'is-visible' );
		// Bir sonraki kareyi bekleyip animasyonu tetikle.
		requestAnimationFrame( function () {
			requestAnimationFrame( function () {
				el.classList.add( 'is-visible' );
			} );
		} );
	}

	/**
	 * Render the result card from the matched rule's page.
	 */
	function renderResult( root, gender, grade ) {
		var resultEl = root.querySelector( '.admissions-finder__result' );
		if ( ! resultEl ) {
			return;
		}

		var i18n = DATA.i18n || {};
		var settings = DATA.settings || {};
		var rule = matchRule( gender, grade );

		// No matching rule, or the rule has no page assigned yet: friendly "no program" card.
		if ( ! rule || ! rule.link ) {
			var fallback = '<div class="admissions-finder__card admissions-finder__card--none">';
			fallback += '<div class="admissions-finder__body">';
			fallback += '<div class="admissions-finder__card-head"><span class="admissions-finder__eyebrow">' + esc( i18n.noResultIntro || '' ) + '</span></div>';
			fallback += '<p class="admissions-finder__short">' + esc( i18n.noResult || '' ) + '</p>';
			fallback += '<div class="admissions-finder__actions">';
			fallback += ctaButton( settings.contactUrl, settings.contactLabel, false );
			fallback += '</div></div></div>';
			resultEl.innerHTML = fallback;
			bindCtaTracking( resultEl, gender, grade );
			reveal( resultEl );
			track( 'program_finder_result', { gender: gender, grade: grade, page: 'none' } );
			return;
		}

		var html = '<div class="admissions-finder__card' + ( rule.image ? ' admissions-finder__card--media' : '' ) + '">';

		if ( rule.image ) {
			html += '<div class="admissions-finder__media">';
			html += '<img src="' + esc( rule.image ) + '" alt="' + esc( rule.title || '' ) + '" loading="lazy" />';
			html += '</div>';
		}

		html += '<div class="admissions-finder__body">';
		html += '<div class="admissions-finder__card-head">';
		html += '<span class="admissions-finder__eyebrow">' + esc( i18n.resultIntro || '' ) + '</span>';
		if ( rule.badge ) {
			html += '<span class="admissions-finder__badge">' + esc( rule.badge ) + '</span>';
		}
		html += '</div>';

		if ( rule.title ) {
			html += '<h3 class="admissions-finder__name">' + esc( rule.title ) + '</h3>';
		}
		if ( rule.excerpt ) {
			html += '<p class="admissions-finder__short">' + esc( rule.excerpt ) + '</p>';
		}
		if ( rule.note ) {
			html += '<p class="admissions-finder__note">' + esc( rule.note ) + '</p>';
		}

		html += '<div class="admissions-finder__actions">';
		html += ctaButton( rule.link, settings.ctaLabel, false );
		// Optional secondary "Get in touch" when a contact link is configured.
		if ( settings.contactUrl ) {
			html += ctaButton( settings.contactUrl, settings.contactLabel, true );
		}
		html += '</div></div></div>';

		resultEl.innerHTML = html;
		bindCtaTracking( resultEl, gender, grade );
		reveal( resultEl );

		track( 'program_finder_result', { gender: gender, grade: grade, href: rule.link } );
	}

	/**
	 * Attach CTA click tracking to the rendered buttons.
	 */
	function bindCtaTracking( resultEl, gender, grade ) {
		var ctas = resultEl.querySelectorAll( '.admissions-finder__cta' );
		Array.prototype.forEach.call( ctas, function ( a ) {
			a.addEventListener( 'click', function () {
				track( 'program_finder_cta_click', { gender: gender, grade: grade, href: a.getAttribute( 'href' ) } );
			} );
		} );
	}

	/**
	 * Bir grup içindeki aktif butonu işaretle.
	 */
	function setActive( buttons, activeBtn ) {
		Array.prototype.forEach.call( buttons, function ( b ) {
			b.setAttribute( 'aria-pressed', b === activeBtn ? 'true' : 'false' );
		} );
	}

	/**
	 * Bir kök öğeyi başlat.
	 */
	function initRoot( root ) {
		var state = { gender: null, grade: null };

		var step2 = root.querySelector( '[data-step="2"]' );
		var genderBtns = root.querySelectorAll( '[data-gender]' );
		var gradeBtns = root.querySelectorAll( '[data-grade]' );

		function maybeRender() {
			if ( state.gender !== null && state.grade !== null ) {
				renderResult( root, state.gender, state.grade );
			}
			syncUrl( state.gender, state.grade );
		}

		function selectGender( btn, silent ) {
			state.gender = btn.getAttribute( 'data-gender' );
			setActive( genderBtns, btn );
			if ( step2 ) {
				step2.hidden = false;
			}
			if ( ! silent ) {
				track( 'program_finder_step1', { gender: state.gender } );
				// Move focus to the first grade option so keyboard users continue naturally.
				var firstGrade = root.querySelector( '[data-grade]' );
				if ( firstGrade ) {
					firstGrade.focus();
				}
			}
			maybeRender();
		}

		function selectGrade( btn, silent ) {
			state.grade = btn.getAttribute( 'data-grade' );
			setActive( gradeBtns, btn );
			if ( ! silent ) {
				track( 'program_finder_step2', { gender: state.gender, grade: state.grade } );
			}
			maybeRender();
		}

		Array.prototype.forEach.call( genderBtns, function ( btn ) {
			btn.addEventListener( 'click', function () {
				selectGender( btn, false );
			} );
		} );

		Array.prototype.forEach.call( gradeBtns, function ( btn ) {
			btn.addEventListener( 'click', function () {
				selectGrade( btn, false );
			} );
		} );

		// Ön seçim: URL parametresi veya data-attribute. Analytics olayı tetiklemez.
		var params = new URLSearchParams( window.location.search );
		var preGender = params.get( 'gender' ) || root.getAttribute( 'data-pre-gender' ) || '';
		var preGrade = params.get( 'grade' );
		if ( preGrade === null ) {
			preGrade = root.getAttribute( 'data-pre-grade' ) || '';
		}

		if ( preGender ) {
			var gBtn = root.querySelector( '[data-gender="' + preGender + '"]' );
			if ( gBtn ) {
				selectGender( gBtn, true );
			}
		}
		if ( preGrade !== '' ) {
			var grBtn = root.querySelector( '[data-grade="' + preGrade + '"]' );
			if ( grBtn ) {
				selectGrade( grBtn, true );
			}
		}
	}

	function init() {
		var roots = document.querySelectorAll( '.admissions-finder' );
		Array.prototype.forEach.call( roots, initRoot );
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
