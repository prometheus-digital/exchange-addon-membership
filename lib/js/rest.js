(function ( ExchangeCommon, $, _, api, Config ) {
	"use strict";

	api.Models.Membership = api.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'memberships', {}, false ),

		_beneficiary: 'ITExchangeAPI.Models.Customer',

		/**
		 * Get the user who is receiving the benefits of this membership.
		 *
		 * @since 1.20.0
		 *
		 * @returns {api.Models.Customer}
		 */
		beneficiary: function () {

			if ( !this.get( 'beneficiary' ) ) {
				return null;
			}

			if ( this._beneficiary instanceof api.Models.Customer == false ) {
				this._beneficiary = new api.Models.Customer( {
					id: this.get( 'beneficiary' )
				} );
			}

			return this._beneficiary;
		},

		/**
		 * Get all available upgrades.
		 *
		 * @since 1.20.0
		 *
		 * @returns {api.Collections.ProrateOffers}
		 */
		upgrades: function () {

			if ( !this._upgrades ) {
				this._upgrades = new api.Collections.ProrateOffers( [], {
					type  : 'upgrade',
					parent: this,
				} );
			}

			return this._upgrades;
		},

		/**
		 * Get all available downgrades.
		 *
		 * @since 1.20.0
		 *
		 * @returns {api.Collections.ProrateOffers}
		 */
		downgrades: function () {

			if ( !this._downgrades ) {
				this._downgrades = new api.Collections.ProrateOffers( [], {
					type  : 'downgrade',
					parent: this,
				} );
			}

			return this._downgrades;
		},
	} );

})( window.ExchangeCommon, jQuery, window._, window.ITExchangeAPI, window.ITExchangeRESTConfig );