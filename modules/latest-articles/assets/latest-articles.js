(function(){
	'use strict';

	function init(root){
		if(!root || root.dataset.kmlaReady === '1') return;
		root.dataset.kmlaReady = '1';

		var cards = Array.prototype.slice.call(root.querySelectorAll('.kmla-card'));
		var buttons = Array.prototype.slice.call(root.querySelectorAll('.kmla-nav-link'));
		var load = root.querySelector('.kmla-load-more');
		var initial = parseInt(root.getAttribute('data-initial') || '6',10);
		var step = parseInt(root.getAttribute('data-load') || '3',10);
		var active = 'all';

		function matches(card, filter){
			if(filter === 'all') return true;
			var cats = (card.getAttribute('data-categories') || '').split(',');
			return cats.indexOf(String(filter)) !== -1;
		}

		function update(){
			var visible = 0;
			var total = 0;
			cards.forEach(function(card){
				var match = matches(card, active);
				if(match){
					total++;
					if(visible < initial) {
						card.classList.remove('kmla-hidden');
						visible++;
					} else {
						card.classList.add('kmla-hidden');
					}
				} else {
					card.classList.add('kmla-hidden');
				}
			});
			if(load){
				load.style.display = total > initial ? '' : 'none';
			}
		}

		buttons.forEach(function(button){
			button.addEventListener('click', function(){
				buttons.forEach(function(b){ b.classList.remove('is-active'); });
				button.classList.add('is-active');
				active = button.getAttribute('data-filter') || 'all';
				initial = parseInt(root.getAttribute('data-initial') || '6',10);
				update();
			});
		});

		if(load){
			load.addEventListener('click', function(){
				var shown = 0;
				cards.forEach(function(card){
					if(matches(card, active) && !card.classList.contains('kmla-hidden')) shown++;
				});
				initial += step;
				update();
				if(load){
					var remaining = cards.some(function(card){
						return matches(card, active) && card.classList.contains('kmla-hidden');
					});
					load.style.display = remaining ? '' : 'none';
				}
			});
		}

		update();
	}

	function scan(){
		document.querySelectorAll('.kmla').forEach(init);
	}

	if(document.readyState === 'loading'){
		document.addEventListener('DOMContentLoaded', scan);
	}else{
		scan();
	}

	if(window.elementorFrontend && window.elementorFrontend.hooks){
		window.elementorFrontend.hooks.addAction('frontend/element_ready/kmpb-latest-articles.default', init);
	}
})();
