<?php
/** This file is part of the wp-forecast plugin for WordPress
 *
 * Copyright 2024  Hans Matzen  (email : webmaster at tuxlog dot de)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package wp-forecast
 */

if ( ! function_exists( 'openweathermap_get_weather_v2' ) ) {
	/**
	 * Get the data from OpenWeatherMap.
	 *
	 * @param strin  $baseuri  the Base URI for OWM.
	 * @param string $apikey   the apikey from OWM.
	 * @param string $lat      the latitude of the location.
	 * @param string $lon      the longitude of the location.
	 * @param string $metric   the units to us.
	 */
	function openweathermap_get_weather_v2( $baseuri, $apikey, $lat, $lon, $metric ) {
		$data = array();

		if ( '1' == $metric ) {
			$metric = 'metric';
		} else {
			$metric = 'imperial';
		}

		// check parms.
		if ( trim( $apikey ) == '' || trim( $lat ) == '' || trim( $lon ) == '' ) {
			return array();
		}
		$url1 = $baseuri . '/weather?lat=' . $lat . '&lon=' . $lon . '&appid=' . $apikey . '&units=' . $metric . '&lang=en';
		$url2 = $baseuri . '/forecast?lat=' . $lat . '&lon=' . $lon . '&appid=' . $apikey . '&units=' . $metric . '&lang=en';

		// Open the file and decode it.
		$file1 = wp_remote_get( $url1 );
		if ( ! is_wp_error( $file1 ) ) {
			$data1 = json_decode( $file1['body'], true );
		}

		$file2 = wp_remote_get( $url2 );
		if ( ! is_wp_error( $file2 ) ) {
			$data2 = json_decode( $file2['body'], true );
		}

		$data['current']  = $data1;
		$data['forecast'] = $data2;

		return $data;
	}
}


if ( ! function_exists( 'openweathermap_get_data_v2' ) ) {
	/**
	 * Extract the weather data from json object returned from OWM.
	 *
	 * @param array $weather_array the array to store the weather.
	 * @param array $wpf_vars       the parameters of the weather widget.
	 */
	function openweathermap_get_data_v2( $weather_array, $wpf_vars ) {
		$w = array();

		if ( '1' == $wpf_vars['metric'] ) {
			$w['un_temp']  = 'C';
			$w['un_dist']  = 'km';
			$w['un_speed'] = 'm/s';
			$w['un_pres']  = 'mb';
			$w['un_prec']  = 'mm';
		} else {
			$w['un_temp']  = 'F';
			$w['un_dist']  = 'mi';
			$w['un_speed'] = 'mph';
			$w['un_pres']  = 'mb';
			$w['un_prec']  = 'in';
		}

		if ( ! isset( $weather_array['current'] ) || ! isset( $weather_array['forecast'] ) ) {
			$w['failure'] = 'No OpenWeathermap data available. ';
			if ( isset( $weather_array['message'] ) ) {
				$w['failure'] .= $weather_array['message'];
			}
			return $w;
		}

		$w['lat']      = $weather_array['current']['coord']['lat'];
		$w['lon']      = $weather_array['current']['coord']['lon'];
		$w['time']     = $weather_array['current']['dt'];
		$w['timezone'] = $weather_array['current']['timezone'];
		$tz_prefix     = '+';

		if ( $w['timezone'] < 0 ) {
			$tz_prefix = '-';
		}

		$mtz = new DateTimeZone( $tz_prefix . abs($w['timezone'] / 36) );

		// current conditions.
		$w['pressure']      = $weather_array['current']['main']['pressure'];
		$w['temperature']   = round( $weather_array['current']['main']['temp'], 0 );
		$w['realfeel']      = round( $weather_array['current']['main']['feels_like'], 0 );
		$w['humidity']      = $weather_array['current']['main']['humidity'];
		$w['weathertext']   = $weather_array['current']['weather'][0]['description'];
		$w['weathericon']   = $weather_array['current']['weather'][0]['icon'];
		$w['weatherid']     = $weather_array['current']['weather'][0]['id'];
		$w['wgusts']        = $weather_array['current']['wind']['speed'];
		$w['windspeed']     = $weather_array['current']['wind']['speed'];
		$w['winddirection'] = $weather_array['current']['wind']['deg'];
		$w['uvindex']       = -1;

		// map precipitation values.
		// init vars.
		$w['precipProbability'] = 0;
		$w['precipIntensity']   = 0;
		$w['precipType']        = '';
		// if it rains add rain volume and set precipitation type to rain.
		if ( isset( $weather_array['current']['rain'] ) ) {
			$w['precipIntensity'] += $weather_array['current']['rain']['1h'];
			$w['precipType']       = 'Rain';
		}
		// if it snows add snow volume and set precipitation type to snow.
		if ( isset( $weather_array['current']['snow'] ) ) {
			$w['precipIntensity'] += $weather_array['current']['snow']['1h'];
			$w['precipType']       = 'Snow';
		}
		// convert mm to inches for compatibility reasons with accuweather.
		$w['precipIntensity'] = $w['precipIntensity'] / 2.54 / 10;

		if ( $w['precipIntensity'] > 0 ) {
			$w['precipProbability'] = 100;
		}

		// sunset sunrise.
		$sr = new DateTime();
		$sr->setTimezone( $mtz );
		$sr->setTimestamp( $weather_array['current']['sys']['sunrise'] );
		$w['sunrise'] = $sr->format( get_option( 'time_format' ) );

		$ss = new DateTime();
		$ss->setTimezone( $mtz );
		$ss->setTimestamp( $weather_array['current']['sys']['sunset'] );
		$w['sunset'] = $ss->format( get_option( 'time_format' ) );

		// forecast.
		$j = 0;
		foreach ( $weather_array['forecast']['list'] as $ff ) {

			if ( substr( $ff['dt_txt'], 11 ) == '00:00:00' ) {
				++$j;
			}
			$odt = new DateTime();
			$odt->setTimezone( $mtz );

			if ( substr( $ff['dt_txt'], 11 ) == '00:00:00' ) {

				$w[ 'fc_nt_short_' . $j ]     = $ff['weather'][0]['description'];
				$w[ 'fc_nt_icon_' . $j ]      = $ff['weather'][0]['icon'];
				$w[ 'fc_nt_iconcode_' . $j ]  = openweathermap_map_icon( $ff['weather'][0]['icon'], true, $wpf_vars['fonticon'] );
				$w[ 'fc_nt_id_' . $j ]        = $ff['weather'][0]['id'];
				$w[ 'fc_nt_htemp_' . $j ]     = round( $ff['main']['temp_max'], 0 );
				$w[ 'fc_nt_ltemp_' . $j ]     = round( $ff['main']['temp_min'], 0 );
				$w[ 'fc_nt_windspeed_' . $j ] = $ff['wind']['speed'];
				$w[ 'fc_nt_winddir_' . $j ]   = $ff['wind']['deg'];
				$w[ 'fc_nt_wgusts_' . $j ]    = $ff['wind']['gust'];
				$w[ 'fc_nt_maxuv_' . $j ]     = -1;
			}

			if ( substr( $ff['dt_txt'], 11 ) == '12:00:00' ) {
				$w[ 'fc_obsdate_' . $j ]      = $ff['dt'] + $odt->getOffset();
				$w[ 'fc_dt_short_' . $j ]     = $ff['weather'][0]['description'];
				$w[ 'fc_dt_icon_' . $j ]      = $ff['weather'][0]['icon'];
				$w[ 'fc_dt_iconcode_' . $j ]  = openweathermap_map_icon( $ff['weather'][0]['icon'], false, $wpf_vars['fonticon'] );
				$w[ 'fc_dt_id_' . $j ]        = $ff['weather'][0]['id'];
				$w[ 'fc_dt_htemp_' . $j ]     = round( $ff['main']['temp_max'], 0 );
				$w[ 'fc_dt_ltemp_' . $j ]     = round( $ff['main']['temp_min'], 0 );
				$w[ 'fc_dt_windspeed_' . $j ] = $ff['wind']['speed'];
				$w[ 'fc_dt_winddir_' . $j ]   = $ff['wind']['deg'];
				$w[ 'fc_dt_wgusts_' . $j ]    = $ff['wind']['gust'];
				$w[ 'fc_dt_maxuv_' . $j ]     = -1;

				// map precipitation values.
				// init vars.
				$w[ 'fc_dt_precipProbability' . $j ] = $ff['pop'] * 100;
				$w[ 'fc_dt_precipIntensity' . $j ]   = 0;
				$w[ 'fc_dt_precipType' . $j ]        = '';
				// if it rains add rain volume and set precipitation type to rain.
				if ( isset( $ff['rain'] ) ) {
					$w[ 'fc_dt_precipIntensity' . $j ] += $ff['rain']['3h'];
					$w[ 'fc_dt_precipType' . $j ]       = 'Rain';
				}
				// if it snows add snow volume and set precipitation type to snow.
				if ( isset( $ff['snow'] ) ) {
					$w[ 'fc_dt_precipIntensity' . $j ] += $ff['snow']['3h'];
					$w[ 'fc_dt_precipType' . $j ]       = 'Snow';
				}

				// convert mm to inches for compatibility reasons with accuweather.
				$w[ 'fc_dt_precipIntensity' . $j ] = $w[ 'fc_dt_precipIntensity' . $j ] / 2.54 / 10;
			}
		}

		// fill failure anyway.
		$w['failure'] = ( isset( $w['failure'] ) ? $w['failure'] : '' );

		return $w;
	}
}

if ( ! function_exists( 'openweathermap_forecast_data_v2' ) ) {
	/**
	 * Return the weather data for the cache from OWM
	 *
	 * @param string $wpfcid            the Widget ID.
	 * @param string $language_override the iso code of the language to use.
	 */
	function openweathermap_forecast_data_v2( $wpfcid = 'A', $language_override = null ) {

		$wpf_vars = get_wpf_opts( $wpfcid );
		if ( ! empty( $language_override ) ) {
			$wpf_vars['wpf_language'] = $language_override;
		}

		$w = maybe_unserialize( wpf_get_option( 'wp-forecast-cache' . $wpfcid ) );

		// get translations.
		global $wpf_lang_dict;
		$wpf_lang = array();
		$langfile = WP_PLUGIN_DIR . '/wp-forecast/widgetlang/wp-forecast-' . strtolower( str_replace( '_', '-', $wpf_vars['wpf_language'] ) ) . '.php';
		if ( file_exists( $langfile ) ) {
			include $langfile;
		}
		$wpf_lang_dict[ $wpf_vars['wpf_language'] ] = $wpf_lang;

		// --------------------------------------------------------------
		// calc values for current conditions.
		if ( isset( $w['failure'] ) && '' != $w['failure'] ) {
			return array( 'failure' => $w['failure'] );
		}

		$w['servicelink'] = 'https://openweathermap.org/weathermap?basemap=map&cities=true&layer=temperature&lat=' . $w['lat'] . '&lon=' . $w['lon'] . '&zoom=5';
		$w['copyright']   = '<a href="https://openweathermap.org">&copy; ' . gmdate( 'Y' ) . ' Powered by OpenWeather</a>';

		// next line is for compatibility.
		$w['acculink'] = $w['servicelink'];
		$w['location'] = $wpf_vars['locname'];
		$w['locname']  = $w['location'];

		// handle empty timezone setting.
		if ( ! isset( $w['timezone'] ) ) {
			$w['timezone'] = get_option( 'timezone_string' );
			$tz            = new DateTimeZone( $w['timezone'] );
		} else {
			$tz_prefix = '+';
			if ( $w['timezone'] < 0 ) {
				$tz_prefix = '-';
			}
			$tz = new DateTimeZone( $tz_prefix . abs($w['timezone'] / 36) );
		}
		$w['gmtdiff'] = $tz->getOffset( new DateTime() );

		$ct = current_time( 'U' );
		$ct = $ct + $wpf_vars['timeoffset'] * 60; // add or subtract time offset.

		$w['blogdate'] = date_i18n( $wpf_vars['fc_date_format'], $ct );
		$w['blogtime'] = date_i18n( $wpf_vars['fc_time_format'], $ct );

		// get date/time from openweathermap.
		$ct            = $w['time'] + $w['gmtdiff'];
		$w['accudate'] = date_i18n( $wpf_vars['fc_date_format'], $ct );
		$w['accutime'] = date_i18n( $wpf_vars['fc_time_format'], $ct );

		$ico            = openweathermap_map_icon( $w['weatherid'], false, $wpf_vars['fonticon'] );
		$iconfile       = find_icon( $ico );
		$w['icon']      = 'icons/' . $iconfile;
		$w['iconcode']  = $ico;
		$w['shorttext'] = wpf__( openweathermap_wcode2text( $w['weatherid'] ), $wpf_vars['wpf_language'] );

		$w['temperature'] = $w['temperature'] . '&deg;' . $w['un_temp'];
		$w['realfeel']    = $w['realfeel'] . '&deg;' . $w['un_temp'];
		$w['humidity']    = round( $w['humidity'], 0 );

		// workaround different pressure values returned by accuweather.
		$press = round( $w['pressure'], 0 );
		if ( strlen( $press ) == 3 && substr( $press, 0, 1 ) == '1' ) {
			$press = $press * 10;
		}
		$w['pressure']     = $press . ' ' . $w['un_pres'];
		$w['humidity']     = round( $w['humidity'], 0 );
		$w['windspeed']    = windstr( $wpf_vars['metric'], $w['windspeed'], $wpf_vars['windunit'] );
		$w['winddir']      = translate_winddir_degree( $w['winddirection'], $wpf_vars['wpf_language'] );
		$w['winddir_orig'] = str_replace( 'O', 'E', $w['winddir'] );
		$w['windgusts']    = windstr( $wpf_vars['metric'], $w['wgusts'], $wpf_vars['windunit'] );

		// calc values for forecast.
		for ( $i = 1; $i < 6; $i++ ) {
			if ( ! isset( $w[ 'fc_obsdate_' . $i ] ) ) {
				continue;
			}
			// daytime forecast.
			$w[ 'fc_obsdate_' . $i ] = date_i18n( $wpf_vars['fc_date_format'], $w[ 'fc_obsdate_' . $i ] );

			$ico                             = openweathermap_map_icon( $w[ 'fc_dt_id_' . $i ], false, $wpf_vars['fonticon'] );
			$iconfile                        = find_icon( $ico );
			$w[ 'fc_dt_icon_' . $i ]         = 'icons/' . $iconfile;
			$w[ 'fc_dt_iconcode_' . $i ]     = $ico;
			$w[ 'fc_dt_desc_' . $i ]         = wpf__( openweathermap_wcode2text( $w[ 'fc_dt_id_' . $i ] ), $wpf_vars['wpf_language'] );
			$w[ 'fc_dt_htemp_' . $i ]        = $w[ 'fc_dt_htemp_' . $i ] . '&deg;' . $w['un_temp'];
			$wstr                            = windstr( $wpf_vars['metric'], $w[ 'fc_dt_windspeed_' . $i ], $wpf_vars['windunit'] );
			$w[ 'fc_dt_windspeed_' . $i ]    = $wstr;
			$w[ 'fc_dt_winddir_' . $i ]      = translate_winddir_degree( $w[ 'fc_dt_winddir_' . $i ], $wpf_vars['wpf_language'] );
			$w[ 'fc_dt_winddir_orig_' . $i ] = str_replace( 'O', 'E', $w[ 'fc_dt_winddir_' . $i ] );
			$w[ 'fc_dt_wgusts_' . $i ]       = windstr( $wpf_vars['metric'], $w[ 'fc_dt_wgusts_' . $i ], $wpf_vars['windunit'] );
			$w[ 'fc_dt_maxuv_' . $i ]        = $w[ 'fc_dt_maxuv_' . $i ];

			// nighttime forecast.
			$ico                             = openweathermap_map_icon( $w[ 'fc_nt_id_' . $i ], true, $wpf_vars['fonticon'] );
			$iconfile                        = find_icon( $ico );
			$w[ 'fc_nt_icon_' . $i ]         = 'icons/' . $iconfile;
			$w[ 'fc_nt_iconcode_' . $i ]     = $ico;
			$w[ 'fc_nt_desc_' . $i ]         = wpf__( openweathermap_wcode2text( $w[ 'fc_nt_id_' . $i ] ), $wpf_vars['wpf_language'] );
			$w[ 'fc_nt_ltemp_' . $i ]        = $w[ 'fc_nt_ltemp_' . $i ] . '&deg;' . $w['un_temp'];
			$wstr                            = windstr( $wpf_vars['metric'], $w[ 'fc_nt_windspeed_' . $i ], $wpf_vars['windunit'] );
			$w[ 'fc_nt_windspeed_' . $i ]    = $wstr;
			$w[ 'fc_nt_winddir_' . $i ]      = translate_winddir_degree( $w[ 'fc_nt_winddir_' . $i ], $wpf_vars['wpf_language'] );
			$w[ 'fc_nt_winddir_orig_' . $i ] = str_replace( 'O', 'E', $w[ 'fc_nt_winddir_' . $i ] );
			$w[ 'fc_nt_wgusts_' . $i ]       = windstr( $wpf_vars['metric'], $w[ 'fc_nt_wgusts_' . $i ], $wpf_vars['windunit'] );
			$w[ 'fc_nt_maxuv_' . $i ]        = $w[ 'fc_nt_maxuv_' . $i ];
		}

		// add hook for possible individual changes.
		$w = apply_filters( 'wp-forecast-openweathermap-data', $w );

		return $w;
	}
}
