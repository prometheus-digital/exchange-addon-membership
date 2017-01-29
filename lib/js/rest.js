(function ( ExchangeCommon, $, _, api, Config ) {
	"use strict";

	api.Models.Membership = api.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'memberships', {}, false ),

		_beneficiary: 'itExchange.api.Models.Customer',
		_transaction: 'itExchange.api.Models.Transaction',

		/**
		 * Get the user who is receiving the benefits of this membership.
		 *
		 * @since 2.0.0
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
		 * Get the transaction that is paying for this membership.
		 *
		 * @since 2.0.0
		 *
		 * @returns {api.Models.Transaction}
		 */
		transaction: function () {

			if ( !this.get( 'transaction' ) ) {
				return null;
			}

			if ( this._transaction instanceof api.Models.Transaction == false ) {
				this._transaction = new api.Models.Transaction( {
					id: this.get( 'transaction' )
				} );
			}

			return this._transaction;
		},
	} );

})( window.itExchange.common, jQuery, window._, window.itExchange.api, window.ITExchangeRESTConfig );