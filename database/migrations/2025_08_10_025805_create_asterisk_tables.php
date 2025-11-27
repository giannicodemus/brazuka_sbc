<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ---- ENUM TYPES (apenas os utilizados por estas tabelas) ----
        DB::statement("DO $$
        BEGIN
            -- boolean-like enum usado em várias tabelas
            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'ast_bool_values') THEN
                CREATE TYPE ast_bool_values AS ENUM ('0','1','off','on','false','true','no','yes');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_auth_type_values_v2') THEN
                CREATE TYPE pjsip_auth_type_values_v2 AS ENUM ('md5','userpass','google_oauth');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_connected_line_method_values') THEN
                CREATE TYPE pjsip_connected_line_method_values AS ENUM ('invite','reinvite','update');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_direct_media_glare_mitigation_values') THEN
                CREATE TYPE pjsip_direct_media_glare_mitigation_values AS ENUM ('none','outgoing','incoming');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_dtmf_mode_values_v3') THEN
                CREATE TYPE pjsip_dtmf_mode_values_v3 AS ENUM ('rfc4733','inband','info','auto','auto_info');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_100rel_values_v2') THEN
                CREATE TYPE pjsip_100rel_values_v2 AS ENUM ('no','required','peer_supported','yes');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_media_encryption_values') THEN
                CREATE TYPE pjsip_media_encryption_values AS ENUM ('no','sdes','dtls');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_timer_values') THEN
                CREATE TYPE pjsip_timer_values AS ENUM ('forced','no','required','yes');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_cid_privacy_values') THEN
                CREATE TYPE pjsip_cid_privacy_values AS ENUM (
                    'allowed_not_screened','allowed_passed_screened','allowed_failed_screened','allowed',
                    'prohib_not_screened','prohib_passed_screened','prohib_failed_screened','prohib','unavailable'
                );
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_dtls_setup_values') THEN
                CREATE TYPE pjsip_dtls_setup_values AS ENUM ('active','passive','actpass');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_redirect_method_values') THEN
                CREATE TYPE pjsip_redirect_method_values AS ENUM ('user','uri_core','uri_pjsip');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_t38udptl_ec_values') THEN
                CREATE TYPE pjsip_t38udptl_ec_values AS ENUM ('none','fec','redundancy');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'sha_hash_values') THEN
                CREATE TYPE sha_hash_values AS ENUM ('SHA-1','SHA-256');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_incoming_call_offer_pref_values') THEN
                CREATE TYPE pjsip_incoming_call_offer_pref_values AS ENUM ('local','local_first','remote','remote_first');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_outgoing_call_offer_pref_values') THEN
                CREATE TYPE pjsip_outgoing_call_offer_pref_values AS ENUM (
                    'local','local_merge','local_first','remote','remote_merge','remote_first'
                );
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'security_negotiation_values') THEN
                CREATE TYPE security_negotiation_values AS ENUM ('no','mediasec');
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_transport_method_values_v2') THEN
                CREATE TYPE pjsip_transport_method_values_v2 AS ENUM (
                    'default','unspecified','tlsv1','tlsv1_1','tlsv1_2','tlsv1_3','sslv2','sslv23','sslv3'
                );
            END IF;

            IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'pjsip_transport_protocol_values_v2') THEN
                CREATE TYPE pjsip_transport_protocol_values_v2 AS ENUM ('udp','tcp','tls','ws','wss','flow');
            END IF;
        END$$;");

        // ---- TABLE: cdr ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS cdr (
                accountcode VARCHAR(80),
                src         VARCHAR(80),
                dst         VARCHAR(80),
                dcontext    VARCHAR(80),
                clid        VARCHAR(80),
                channel     VARCHAR(80),
                dstchannel  VARCHAR(80),
                lastapp     VARCHAR(80),
                lastdata    VARCHAR(80),
                start       TIMESTAMP WITHOUT TIME ZONE,
                answer      TIMESTAMP WITHOUT TIME ZONE,
                \"end\"     TIMESTAMP WITHOUT TIME ZONE,
                duration    INTEGER,
                billsec     INTEGER,
                disposition VARCHAR(45),
                amaflags    VARCHAR(45),
                userfield   VARCHAR(256),
                uniqueid    VARCHAR(150),
                linkedid    VARCHAR(150),
                peeraccount VARCHAR(80),
                sequence    INTEGER
            );
        ");

        // ---- TABLE: ps_aors ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_aors (
                id                   VARCHAR(255) NOT NULL,
                contact              VARCHAR(255),
                default_expiration   INTEGER,
                mailboxes            VARCHAR(80),
                max_contacts         INTEGER,
                minimum_expiration   INTEGER,
                remove_existing      ast_bool_values,
                qualify_frequency    INTEGER,
                authenticate_qualify ast_bool_values,
                maximum_expiration   INTEGER,
                outbound_proxy       VARCHAR(255),
                support_path         ast_bool_values,
                qualify_timeout      DOUBLE PRECISION,
                voicemail_extension  VARCHAR(40),
                remove_unavailable   ast_bool_values,
                qualify_2xx_only     ast_bool_values
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_aors_id_key ON ps_aors (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_aors_id ON ps_aors (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_aors_qualifyfreq_contact ON ps_aors (qualify_frequency, contact);");

        // ---- TABLE: ps_auths ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_auths (
                id                       VARCHAR(255) NOT NULL,
                auth_type                pjsip_auth_type_values_v2,
                nonce_lifetime           INTEGER,
                md5_cred                 VARCHAR(40),
                password                 VARCHAR(80),
                realm                    VARCHAR(255),
                username                 VARCHAR(40),
                refresh_token            VARCHAR(255),
                oauth_clientid           VARCHAR(255),
                oauth_secret             VARCHAR(255),
                password_digest          VARCHAR(1024),
                supported_algorithms_uas VARCHAR(1024),
                supported_algorithms_uac VARCHAR(1024)
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_auths_id_key ON ps_auths (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_auths_id ON ps_auths (id);");

        // ---- TABLE: ps_endpoint_id_ips ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_endpoint_id_ips (
                id                VARCHAR(255) NOT NULL,
                endpoint          VARCHAR(255),
                match             VARCHAR(80),
                srv_lookups       ast_bool_values,
                match_header      VARCHAR(255),
                match_request_uri VARCHAR(255)
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_endpoint_id_ips_id_key ON ps_endpoint_id_ips (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_endpoint_id_ips_id ON ps_endpoint_id_ips (id);");

        // ---- TABLE: ps_endpoints ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_endpoints (
                id                                 VARCHAR(255) NOT NULL,
                transport                          VARCHAR(40),
                aors                               VARCHAR(2048),
                auth                               VARCHAR(255),
                context                            VARCHAR(40),
                disallow                           VARCHAR(200),
                allow                              VARCHAR(200),
                direct_media                       ast_bool_values,
                connected_line_method              pjsip_connected_line_method_values,
                direct_media_method                pjsip_connected_line_method_values,
                direct_media_glare_mitigation      pjsip_direct_media_glare_mitigation_values,
                disable_direct_media_on_nat        ast_bool_values,
                dtmf_mode                          pjsip_dtmf_mode_values_v3,
                external_media_address             VARCHAR(40),
                force_rport                        ast_bool_values,
                ice_support                        ast_bool_values,
                identify_by                        VARCHAR(80),
                mailboxes                          VARCHAR(40),
                moh_suggest                        VARCHAR(40),
                outbound_auth                      VARCHAR(255),
                outbound_proxy                     VARCHAR(255),
                rewrite_contact                    ast_bool_values,
                rtp_ipv6                           ast_bool_values,
                rtp_symmetric                      ast_bool_values,
                send_diversion                     ast_bool_values,
                send_pai                           ast_bool_values,
                send_rpid                          ast_bool_values,
                timers_min_se                      INTEGER,
                timers                             pjsip_timer_values,
                timers_sess_expires                INTEGER,
                callerid                           VARCHAR(40),
                callerid_privacy                   pjsip_cid_privacy_values,
                callerid_tag                       VARCHAR(40),
                \"100rel\"                         pjsip_100rel_values_v2,
                aggregate_mwi                      ast_bool_values,
                trust_id_inbound                   ast_bool_values,
                trust_id_outbound                  ast_bool_values,
                use_ptime                          ast_bool_values,
                use_avpf                           ast_bool_values,
                media_encryption                   pjsip_media_encryption_values,
                inband_progress                    ast_bool_values,
                call_group                         VARCHAR(40),
                pickup_group                       VARCHAR(40),
                named_call_group                   VARCHAR(40),
                named_pickup_group                 VARCHAR(40),
                device_state_busy_at               INTEGER,
                fax_detect                         ast_bool_values,
                t38_udptl                          ast_bool_values,
                t38_udptl_ec                       pjsip_t38udptl_ec_values,
                t38_udptl_maxdatagram              INTEGER,
                t38_udptl_nat                      ast_bool_values,
                t38_udptl_ipv6                     ast_bool_values,
                tone_zone                          VARCHAR(40),
                language                           VARCHAR(40),
                one_touch_recording                ast_bool_values,
                record_on_feature                  VARCHAR(40),
                record_off_feature                 VARCHAR(40),
                rtp_engine                         VARCHAR(40),
                allow_transfer                     ast_bool_values,
                allow_subscribe                    ast_bool_values,
                sdp_owner                          VARCHAR(40),
                sdp_session                        VARCHAR(40),
                tos_audio                          VARCHAR(10),
                tos_video                          VARCHAR(10),
                sub_min_expiry                     INTEGER,
                from_domain                        VARCHAR(40),
                from_user                          VARCHAR(40),
                mwi_from_user                      VARCHAR(40),
                dtls_verify                        VARCHAR(40),
                dtls_rekey                         VARCHAR(40),
                dtls_cert_file                     VARCHAR(200),
                dtls_private_key                   VARCHAR(200),
                dtls_cipher                        VARCHAR(200),
                dtls_ca_file                       VARCHAR(200),
                dtls_ca_path                       VARCHAR(200),
                dtls_setup                         pjsip_dtls_setup_values,
                srtp_tag_32                        ast_bool_values,
                media_address                      VARCHAR(40),
                redirect_method                    pjsip_redirect_method_values,
                set_var                            TEXT,
                cos_audio                          INTEGER,
                cos_video                          INTEGER,
                message_context                    VARCHAR(40),
                force_avp                          ast_bool_values,
                media_use_received_transport       ast_bool_values,
                accountcode                        VARCHAR(80),
                user_eq_phone                      ast_bool_values,
                moh_passthrough                    ast_bool_values,
                media_encryption_optimistic        ast_bool_values,
                rpid_immediate                     ast_bool_values,
                g726_non_standard                  ast_bool_values,
                rtp_keepalive                      INTEGER,
                rtp_timeout                        INTEGER,
                rtp_timeout_hold                   INTEGER,
                bind_rtp_to_media_address          ast_bool_values,
                voicemail_extension                VARCHAR(40),
                mwi_subscribe_replaces_unsolicited ast_bool_values,
                deny                               VARCHAR(95),
                permit                             VARCHAR(95),
                acl                                VARCHAR(40),
                contact_deny                       VARCHAR(95),
                contact_permit                     VARCHAR(95),
                contact_acl                        VARCHAR(40),
                subscribe_context                  VARCHAR(40),
                fax_detect_timeout                 INTEGER,
                contact_user                       VARCHAR(80),
                preferred_codec_only               ast_bool_values,
                asymmetric_rtp_codec               ast_bool_values,
                rtcp_mux                           ast_bool_values,
                allow_overlap                      ast_bool_values,
                refer_blind_progress               ast_bool_values,
                notify_early_inuse_ringing         ast_bool_values,
                max_audio_streams                  INTEGER,
                max_video_streams                  INTEGER,
                webrtc                             ast_bool_values,
                dtls_fingerprint                   sha_hash_values,
                incoming_mwi_mailbox               VARCHAR(40),
                bundle                             ast_bool_values,
                dtls_auto_generate_cert            ast_bool_values,
                follow_early_media_fork            ast_bool_values,
                accept_multiple_sdp_answers        ast_bool_values,
                suppress_q850_reason_headers       ast_bool_values,
                trust_connected_line               ast_bool_values,
                send_connected_line                ast_bool_values,
                ignore_183_without_sdp             ast_bool_values,
                codec_prefs_incoming_offer         VARCHAR(128),
                codec_prefs_outgoing_offer         VARCHAR(128),
                codec_prefs_incoming_answer        VARCHAR(128),
                codec_prefs_outgoing_answer        VARCHAR(128),
                stir_shaken                        ast_bool_values,
                send_history_info                  ast_bool_values,
                allow_unauthenticated_options      ast_bool_values,
                t38_bind_udptl_to_media_address    ast_bool_values,
                geoloc_incoming_call_profile       VARCHAR(80),
                geoloc_outgoing_call_profile       VARCHAR(80),
                incoming_call_offer_pref           pjsip_incoming_call_offer_pref_values,
                outgoing_call_offer_pref           pjsip_outgoing_call_offer_pref_values,
                stir_shaken_profile                VARCHAR(80),
                security_negotiation               security_negotiation_values,
                security_mechanisms                VARCHAR(512),
                send_aoc                           ast_bool_values,
                overlap_context                    VARCHAR(80),
                tenantid                           VARCHAR(80),
                suppress_moh_on_sendonly           ast_bool_values
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_endpoints_id_key ON ps_endpoints (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_endpoints_id ON ps_endpoints (id);");

        // ---- TABLE: ps_registrations ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_registrations (
                id                       VARCHAR(255) NOT NULL,
                auth_rejection_permanent ast_bool_values,
                client_uri               VARCHAR(255),
                contact_user             VARCHAR(40),
                expiration               INTEGER,
                max_retries              INTEGER,
                outbound_auth            VARCHAR(255),
                outbound_proxy           VARCHAR(255),
                retry_interval           INTEGER,
                forbidden_retry_interval INTEGER,
                server_uri               VARCHAR(255),
                transport                VARCHAR(40),
                support_path             ast_bool_values,
                fatal_retry_interval     INTEGER,
                line                     ast_bool_values,
                endpoint                 VARCHAR(255),
                support_outbound         ast_bool_values,
                contact_header_params    VARCHAR(255),
                max_random_initial_delay INTEGER,
                security_negotiation     security_negotiation_values,
                security_mechanisms      VARCHAR(512),
                user_agent               VARCHAR(255)
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_registrations_id_key ON ps_registrations (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_registrations_id ON ps_registrations (id);");

        // ---- TABLE: ps_transports ----
        DB::statement("
            CREATE TABLE IF NOT EXISTS ps_transports (
                id                          VARCHAR(40) NOT NULL,
                async_operations            INTEGER,
                bind                        VARCHAR(40),
                ca_list_file                VARCHAR(200),
                cert_file                   VARCHAR(200),
                cipher                      VARCHAR(200),
                domain                      VARCHAR(40),
                external_media_address      VARCHAR(40),
                external_signaling_address  VARCHAR(40),
                external_signaling_port     INTEGER,
                method                      pjsip_transport_method_values_v2,
                local_net                   VARCHAR(40),
                password                    VARCHAR(40),
                priv_key_file               VARCHAR(200),
                protocol                    pjsip_transport_protocol_values_v2,
                require_client_cert         ast_bool_values,
                verify_client               ast_bool_values,
                verify_server               ast_bool_values,
                tos                         VARCHAR(10),
                cos                         INTEGER,
                allow_reload                ast_bool_values,
                symmetric_transport         ast_bool_values,
                allow_wildcard_certs        ast_bool_values,
                tcp_keepalive_enable        BOOLEAN,
                tcp_keepalive_idle_time     INTEGER,
                tcp_keepalive_interval_time INTEGER,
                tcp_keepalive_probe_count   INTEGER
            );
        ");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS ps_transports_id_key ON ps_transports (id);");
        DB::statement("CREATE INDEX IF NOT EXISTS ps_transports_id ON ps_transports (id);");
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables first (dependem dos tipos)
        DB::statement("DROP TABLE IF EXISTS ps_transports;");
        DB::statement("DROP TABLE IF EXISTS ps_registrations;");
        DB::statement("DROP TABLE IF EXISTS ps_endpoints;");
        DB::statement("DROP TABLE IF EXISTS ps_endpoint_id_ips;");
        DB::statement("DROP TABLE IF EXISTS ps_auths;");
        DB::statement("DROP TABLE IF EXISTS ps_aors;");
        DB::statement("DROP TABLE IF EXISTS cdr;");

        // Agora os tipos (somente se não houver dependências)
        // Ordem inversa não é crítica aqui, mas ajuda.
        DB::statement("DO $$
        BEGIN
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_transport_protocol_values_v2') THEN
                DROP TYPE pjsip_transport_protocol_values_v2;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_transport_method_values_v2') THEN
                DROP TYPE pjsip_transport_method_values_v2;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='security_negotiation_values') THEN
                DROP TYPE security_negotiation_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_outgoing_call_offer_pref_values') THEN
                DROP TYPE pjsip_outgoing_call_offer_pref_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_incoming_call_offer_pref_values') THEN
                DROP TYPE pjsip_incoming_call_offer_pref_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='sha_hash_values') THEN
                DROP TYPE sha_hash_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_t38udptl_ec_values') THEN
                DROP TYPE pjsip_t38udptl_ec_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_redirect_method_values') THEN
                DROP TYPE pjsip_redirect_method_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_dtls_setup_values') THEN
                DROP TYPE pjsip_dtls_setup_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_cid_privacy_values') THEN
                DROP TYPE pjsip_cid_privacy_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_timer_values') THEN
                DROP TYPE pjsip_timer_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_media_encryption_values') THEN
                DROP TYPE pjsip_media_encryption_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_100rel_values_v2') THEN
                DROP TYPE pjsip_100rel_values_v2;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_dtmf_mode_values_v3') THEN
                DROP TYPE pjsip_dtmf_mode_values_v3;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_direct_media_glare_mitigation_values') THEN
                DROP TYPE pjsip_direct_media_glare_mitigation_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_connected_line_method_values') THEN
                DROP TYPE pjsip_connected_line_method_values;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='pjsip_auth_type_values_v2') THEN
                DROP TYPE pjsip_auth_type_values_v2;
            END IF;
            IF EXISTS (SELECT 1 FROM pg_type WHERE typname='ast_bool_values') THEN
                DROP TYPE ast_bool_values;
            END IF;
        END$$;");
    }
};
