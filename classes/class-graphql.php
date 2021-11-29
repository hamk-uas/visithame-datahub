<?php

/**
 * Sample queries
 * {
 *     productInformations {
 *         description
 *         name
 *     }
 *     productTags {
 *         tag
 *     }
 *     company {
 *         businessName
 *     }
 * }

 * $body_to_send = array(
 *     'query' => '{
 *         product(
 *         where: {
 *             productTags: {
 *                 tag: {
 *                     _in: ["camping", "luxury"]
 *                 }
 *             }
 *             postalAddresses: {
 *                 city: 
 *                         _in: [
 *                             "tammela"
 *                             
 *                 }
 *             }
 *         })
 *         {
 *             productInformations {
 *                 description
 *                 name
 *             }
 *         }
 *     }'
 * );
 */
class GraphQL
{

    function get_access_token($datahub_client, $datahub_username, $datahub_password)
    {
        $access_token = get_option('access_token');

        if (empty($access_token)) {

            $request = wp_remote_post('https://iam-datahub.visitfinland.com/auth/realms/Datahub/protocol/openid-connect/token', [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'body' => [
                    'client_id' => 'datahub-api',
                    'client_secret' => $datahub_client, 
                    'grant_type' => 'password',
                    'username' => $datahub_username, 
                    'password' => $datahub_password 
                ]
            ]);

            if (is_wp_error($request)) {
                return false;
            }

            $body = wp_remote_retrieve_body($request);
            $response_code = wp_remote_retrieve_response_code($request);

            $data = json_decode($body);

            if ($response_code == 401) {
                echo wp_remote_retrieve_response_message($request);
                return false;
            }

            $access_token = $data->access_token;

            add_option('access_token', $access_token);
        }

        return $access_token;
    }

    /**
     * Function for POSTing the GraphQL API with provided access token and body
     */
    function post_body($access_token, $body_to_send)
    {
        $request = wp_remote_post('https://api-datahub.visitfinland.com/graphql/v1/graphql', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ],
            'method' => 'POST',
            'body' => json_encode($body_to_send),
            'data_format' => 'body',
            'blocking' => true,
        ]);

        if (is_wp_error($request)) {
            delete_option('access_token');
            return false;
        }

        $body = wp_remote_retrieve_body($request);
        $data = json_decode($body);

        return $data;
    }

    function get_products($tags, $city_codes, $access_token)
    {
        $tags = explode(',', $tags);
        $tags = '"' . implode("\",\"", $tags) . '"';

        $city_codes = explode(',', $city_codes);
        $city_codes = '"' . implode("\",\"", $city_codes) . '"';

        $body_to_send = array(
            'query' => '{
            product(
            where: {
                postalAddresses: {
                    postalArea: {
                        cityCode: {
                            _in: [
                                "061",
                                "082",
                                "086",
                                "103",
                                "109",
                                "165",
                                "169",
                                "433",
                                "694",
                                "834",
                                "981"
                                ]
                            }
                    }
                }
            })
            {
                id
                companyId
                accessible
                type
                urlPrimary
                webshopUrlPrimary
                updatedAt
                productImages {
                    largeUrl
                    thumbnailUrl
                    copyright
                    coverPhoto
                    altText
                    orientation
                }
                productInformations {
                    name
                    description
                    language
                    updatedAt
                    url
                    webshopUrl
                }
                company {
                    id
                    businessName
                    officialName
                    logoUrl
                    logoThumbnailUrl
                    webshopUrl
                    websiteUrl
                    updatedAt
                    socialMediaLinks {
                        companyId
                        linkType
                        url
                    }
                }
                postalAddresses {
                    location
                    postalCode
                    streetName
                    city
                    updatedAt
                    postalArea {
                        cityCode
                        postalArea
                        postalCode
                    }
                }
                productVideos {
                    title
                    url
                }
                productPricings {
                    toPrice
                    fromPrice
                    updatedAt
                }
                productTags {
                    tag
                    updatedAt
                }
                productAvailableMonths {
                    month
                    updatedAt
                }
                openingHours {
                    openFrom
                    openTo
                    weekday
                    updatedAt
                }
                contactDetails {
                    email
                    id
                    phone
                }
                productTargetGroups {
                    targetGroupId
                    updatedAt
                }
            }
        }'
        );

        $data = $this->post_body($access_token, $body_to_send);

        return $data;
    }

    function get_tags($access_token)
    {
        $body_to_send = array(
            'query' => '{
            tags {
                tag
            }
        }'
        );

        $data = $this->post_body($access_token, $body_to_send);

        return $data;
    }

    function get_municipalities()
    {
        $database = new DataHubDatabase();
        $datahub_options = $database->get_datahub_options();
        $access_token = $this->get_access_token($datahub_options['datahub_client_secret'], $datahub_options['datahub_username'], $datahub_options['datahub_password']);

        if ($access_token == false) {
            return "Can't get access token";
        }

        $body_to_send = array(
            'query' => '{
            postalAddress(distinct_on: city) {
                city
                postalArea {
                    cityCode
                }
            }
        }'
        );

        $data = $this->post_body($access_token, $body_to_send);
        $data = $data->data->postalAddress;

        foreach ($data as $key) {
            $key->checked = false;
            $key->city = ucfirst(mb_strtolower(trim($key->city), 'UTF-8'));
            $key->city_code = $key->postalArea->cityCode;
        }

        $data = $this->remove_duplicate_municipalities($data);

        foreach ($data as $key) {
            $database->insert_or_update_editor_municipality($key->city, $key->city_code);
        }

        return $data;
    }

    function remove_duplicate_municipalities($municipalities)
    {
        $new_municipalities = array_map(function ($municipality) {
            return $municipality->postalArea->cityCode;
        }, $municipalities);

        $unique_municipalities = array_unique($new_municipalities);

        return array_values(array_intersect_key($municipalities, $unique_municipalities));
    }
}
