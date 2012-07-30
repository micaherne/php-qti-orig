(function($) {
	
	// Note to self: remember jQuery data attributes only accessible
	// using lower case name!
	
	$.phpqti = {
		'init': function() {
			$('.qti_endAttemptInteraction').endAttemptInteraction();
			$('.qti_sliderInteraction').sliderInteraction();
		}	
	};
	
	$.widget ( "phpqti.endAttemptInteraction", {
		
		options: {
			
		},
		
		_create: function() {
			var self = this;
			
			self.varname = self.element.attr('id').replace(/^endAttemptInteraction_/, '');

			$(self.element).on('click', 'input:submit', function(e){
				$(self.element).find('input:hidden').val('true');
			});
		}
		
	});
	
	$.widget ( "phpqti.sliderInteraction", {
		
		options: {
			
		},
		
		_create: function() {
			var self = this;
			var el = $(self.element);
			var input = $('input:hidden', el);
			var valueElement = $('.value', el);
			
			var sliderOpts = {
				min: el.data('lowerbound'),
				max: el.data('upperbound'),
				range: 'min',
				slide: function(event, ui) {
					input.val(ui.value);
					valueElement.text(ui.value);
				}
			};
			
			if (input.val()) {
				sliderOpts.value = input.val();
				valueElement.text(input.val());
			}
			
			if (el.data('step')) {
				sliderOpts.step = el.data('step');
			}
			
			if (el.data('orientation')) {
				sliderOpts.orientation = el.data('orientation');
			}
			
			$('.slider', el).slider(sliderOpts);
		}
		
	});
	
}(jQuery));