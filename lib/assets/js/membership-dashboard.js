(function ( $, api, common, config ) {
	$( '.it-exchange-content-group-toggle' ).on( 'click', '.it-exchange-group-content-label', function ( event ) {
		$( this ).parent().toggleClass( 'open' ).find( 'ul' ).toggleClass( 'it-exchange-hidden' );
	} );

	$( document ).ready( function () {
		var userMembership = new api.Models.Membership( config.userMembership );

		if ( config.upgrades.length || config.downgrades.length ) {
			app.start( userMembership, config.upgrades, config.downgrades );
		}
	} );

	var app = window.ITExchangeChangeMyMembership = {
		Views: [],
		View : null,

		cartLoaded: false,
		cart      : null,

		start: function ( userMembership, upgrades, downgrades ) {

			upgrades = new api.Collections.ProrateOffers( upgrades, {
				parent: userMembership,
				type  : 'upgrade',
			} );

			downgrades = new api.Collections.ProrateOffers( downgrades, {
				parent: userMembership,
				type  : 'downgrade',
			} );

			this.View = new app.Views.ChangeMyMembership( {
				model     : userMembership,
				upgrades  : upgrades,
				downgrades: downgrades,
			} );
			this.View.inject( '#it-exchange-change-my-membership' );

			api.loadCart().done( function () {
				app.cartLoaded = true;
			} );
		},
	};

	app.Views.ChangeMyMembership = api.View.extend( {
		template: wp.template( 'it-exchange-change-my-membership' ),

		events: {
			'click .it-exchange-change-my-membership--trigger': 'begin'
		},

		initialize: function ( options ) {

			_.each( options.upgrades.models, (function ( model ) {
				this.addOfferView( model, 'upgrade' );
			}).bind( this ) );

			_.each( options.downgrades.models, (function ( model ) {
				this.addOfferView( model, 'downgrade' );
			}).bind( this ) );
		},

		render: function () {
			this.$el.html( this.template( { i18n: config.i18n } ) );
			this.$( '.it-exchange-membership-updowngrade-options' ).hide();
			this.views.render();
		},

		begin: function () {
			this.$( '.it-exchange-change-my-membership--trigger' ).hide();
			this.$( '.it-exchange-membership-updowngrade-options' ).show();

			if ( !app.cart ) {

				if ( app.cartLoaded ) {
					this.createCart();
				} else {
					var i = setInterval( (function () {
						if ( app.cartLoaded ) {
							clearInterval( i );
							this.createCart();
						}
					}).bind( this ), 50 );
				}
			}
		},

		createCart: function () {
			api.createCart( '', { is_main: false } ).done( (function ( cart ) {
				this.$( '.it-exchange-change-my-membership-offer--trigger' ).removeAttr( 'disabled' );
				app.cart = cart;
			}).bind( this ) );
		},

		/**
		 * Add an offer view.
		 *
		 * @param {api.Models.ProrateOffer} model
		 * @param {String} type
		 */
		addOfferView: function ( model, type ) {
			this.views.add( '.it-exchange-membership-updowngrade-options', new app.Views.Offer( {
				model: model,
				type : type,
			} ) )
		},
	} );

	app.Views.Offer = api.View.extend( {
		template : wp.template( 'it-exchange-change-my-membership-offer' ),
		tagName  : 'li',
		className: 'it-exchange-membership-updowngrade-item',
		events   : {
			'click .it-exchange-change-my-membership-offer--trigger': 'continueToPayment',
			'click. .it-exchange-change-my-membership-offer--cancel': 'cancelPayment'
		},

		type    : 'upgrade',
		lineItem: null,

		initialize: function ( options ) {
			this.type = options.type;
		},

		render: function () {

			var attr = this.model.toJSON();
			attr.prorateType = this.type;
			attr.i18n = config.i18n;

			this.$el.html( this.template( attr ) );
			this.$( '.it-exchange-change-my-membership-offer--cancel' ).hide();

			if ( app.cart ) {
				this.$( '.it-exchange-change-my-membership-offer--trigger' ).removeAttr( 'disabled' );
			}
		},

		continueToPayment: function ( e ) {
			var target = $( e.target );
			target.attr( 'disabled', true );

			this.$( '.it-exchange-change-my-membership-offer--cancel' ).show();

			if ( !app.cart ) {
				return;
			}

			this.model.accept( app.cart ).done( (function ( lineItem ) {

				this.$( '.it-exchange-change-my-membership-offer--trigger' ).hide();

				this.lineItem = lineItem;
				var checkout = new api.Views.Checkout( {
					model: app.cart,
				} );

				checkout.inject( this.$( '.it-exchange-change-my-membership-offer--checkout-container' ) );
			}).bind( this ) );
		},

		cancelPayment: function () {

			this.$( '.it-exchange-change-my-membership-offer--trigger' ).removeAttr( 'disabled' ).show();
			this.$( '.it-exchange-change-my-membership-offer--cancel' ).hide();
			this.$( '.it-exchange-change-my-membership-offer--checkout-container' ).remove();

			this.lineItem.destroy();
		}
	} );

})( jQuery, window.ITExchangeAPI, window.ExchangeCommon, window.ITExchangeMembershipPublic );