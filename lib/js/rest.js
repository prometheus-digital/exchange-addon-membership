(function ( ExchangeCommon, $, _, api, Config ) {
	"use strict";

	api.Models.Membership = api.Model.extend( {
		urlRoot: ExchangeCommon.getRestUrl( 'memberships', {}, false ),

		_beneficiary: 'ITExchangeAPI.Models.Customer',

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
	} );

})( window.ExchangeCommon, jQuery, window._, window.ITExchangeAPI, window.ITExchangeRESTConfig );