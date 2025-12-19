--
-- PostgreSQL database dump
--

\restrict 0U5u70cZDlwS6rPlYjCtoPp9cMrxRcr3kecBfcWYQABHKfPpgA8fDKJxKdnmOgz

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-12-19 09:16:54

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 269 (class 1255 OID 66589)
-- Name: check_equipment_availability(integer, integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_equipment_availability(p_equipment_id integer, p_quantity integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN EXISTS (
        SELECT 1 
        FROM equipment 
        WHERE id = p_equipment_id 
        AND is_active = true 
        AND available_quantity >= p_quantity
    );
END;
$$;


ALTER FUNCTION public.check_equipment_availability(p_equipment_id integer, p_quantity integer) OWNER TO postgres;

--
-- TOC entry 3748 (class 0 OID 0)
-- Dependencies: 269
-- Name: FUNCTION check_equipment_availability(p_equipment_id integer, p_quantity integer); Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON FUNCTION public.check_equipment_availability(p_equipment_id integer, p_quantity integer) IS 'Check if equipment has sufficient available quantity';


--
-- TOC entry 272 (class 1255 OID 66671)
-- Name: check_student_checkin_today(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_student_checkin_today(p_student_id integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Return TRUE if NO check-in record exists for today
    RETURN NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'datang'
    );
END;
$$;


ALTER FUNCTION public.check_student_checkin_today(p_student_id integer) OWNER TO postgres;

--
-- TOC entry 273 (class 1255 OID 66672)
-- Name: check_student_checkout_today(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.check_student_checkout_today(p_student_id integer) RETURNS boolean
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Must have checked in today
    IF NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'datang'
    ) THEN
        RETURN FALSE; -- Cannot check out if haven't checked in
    END IF;
    
    -- Must NOT have checked out yet
    RETURN NOT EXISTS (
        SELECT 1 
        FROM student_attendance 
        WHERE student_id = p_student_id 
        AND attendance_date = CURRENT_DATE 
        AND attendance_type = 'pulang'
    );
END;
$$;


ALTER FUNCTION public.check_student_checkout_today(p_student_id integer) OWNER TO postgres;

--
-- TOC entry 291 (class 1255 OID 74959)
-- Name: fn_delete_attendance(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_delete_attendance(p_attendance_id integer) RETURNS TABLE(p_success boolean, p_message text)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_delete_attendance(
        p_attendance_id,
        v_success,
        v_message
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;


ALTER FUNCTION public.fn_delete_attendance(p_attendance_id integer) OWNER TO postgres;

--
-- TOC entry 287 (class 1255 OID 74960)
-- Name: fn_get_attendance_stats(date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_get_attendance_stats(p_date date) RETURNS TABLE(p_total_checkins integer, p_total_checkouts integer, p_unique_students integer, p_incomplete_count integer, p_complete_count integer)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_checkins INTEGER;
    v_checkouts INTEGER;
    v_unique INTEGER;
    v_incomplete INTEGER;
    v_complete INTEGER;
BEGIN
    -- Call stored procedure
    CALL sp_get_attendance_stats(
        p_date,
        v_checkins,
        v_checkouts,
        v_unique,
        v_incomplete,
        v_complete
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_checkins, v_checkouts, v_unique, v_incomplete, v_complete;
END;
$$;


ALTER FUNCTION public.fn_get_attendance_stats(p_date date) OWNER TO postgres;

--
-- TOC entry 288 (class 1255 OID 74961)
-- Name: fn_get_student_attendance_summary(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_get_student_attendance_summary(p_student_id integer) RETURNS TABLE(p_total_days integer, p_complete_days integer, p_incomplete_days integer, p_avg_duration_minutes numeric, p_total_duration_hours numeric)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_total_days INTEGER;
    v_complete INTEGER;
    v_incomplete INTEGER;
    v_avg_duration NUMERIC;
    v_total_hours NUMERIC;
BEGIN
    -- Call stored procedure
    CALL sp_get_student_attendance_summary(
        p_student_id,
        v_total_days,
        v_complete,
        v_incomplete,
        v_avg_duration,
        v_total_hours
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_total_days, v_complete, v_incomplete, v_avg_duration, v_total_hours;
END;
$$;


ALTER FUNCTION public.fn_get_student_attendance_summary(p_student_id integer) OWNER TO postgres;

--
-- TOC entry 289 (class 1255 OID 74957)
-- Name: fn_student_checkin(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_student_checkin(p_student_id integer, p_ip_address character varying DEFAULT NULL::character varying, p_user_agent character varying DEFAULT NULL::character varying) RETURNS TABLE(p_success boolean, p_message text)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_student_checkin(
        p_student_id,
        v_success,
        v_message,
        p_ip_address,
        p_user_agent
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;


ALTER FUNCTION public.fn_student_checkin(p_student_id integer, p_ip_address character varying, p_user_agent character varying) OWNER TO postgres;

--
-- TOC entry 290 (class 1255 OID 74958)
-- Name: fn_student_checkout(integer, character varying, character varying); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_student_checkout(p_student_id integer, p_ip_address character varying DEFAULT NULL::character varying, p_user_agent character varying DEFAULT NULL::character varying) RETURNS TABLE(p_success boolean, p_message text)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_success BOOLEAN;
    v_message TEXT;
BEGIN
    -- Call stored procedure
    CALL sp_student_checkout(
        p_student_id,
        v_success,
        v_message,
        p_ip_address,
        p_user_agent
    );
    
    -- Return hasil
    RETURN QUERY SELECT v_success, v_message;
END;
$$;


ALTER FUNCTION public.fn_student_checkout(p_student_id integer, p_ip_address character varying, p_user_agent character varying) OWNER TO postgres;

--
-- TOC entry 271 (class 1255 OID 66670)
-- Name: get_student_attendance_summary(integer, date, date); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_student_attendance_summary(p_student_id integer, p_start_date date DEFAULT NULL::date, p_end_date date DEFAULT NULL::date) RETURNS TABLE(total_days bigint, complete_days bigint, checkin_only bigint, checkout_only bigint)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        COUNT(DISTINCT attendance_date) as total_days,
        COUNT(DISTINCT CASE 
            WHEN EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'pulang'
            ) THEN attendance_date 
        END) as complete_days,
        COUNT(DISTINCT CASE 
            WHEN attendance_type = 'datang' 
            AND NOT EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'pulang'
            ) THEN attendance_date 
        END) as checkin_only,
        COUNT(DISTINCT CASE 
            WHEN attendance_type = 'pulang' 
            AND NOT EXISTS (
                SELECT 1 FROM student_attendance sa2 
                WHERE sa2.student_id = p_student_id 
                AND sa2.attendance_date = sa.attendance_date 
                AND sa2.attendance_type = 'datang'
            ) THEN attendance_date 
        END) as checkout_only
    FROM student_attendance sa
    WHERE student_id = p_student_id
    AND (p_start_date IS NULL OR attendance_date >= p_start_date)
    AND (p_end_date IS NULL OR attendance_date <= p_end_date);
END;
$$;


ALTER FUNCTION public.get_student_attendance_summary(p_student_id integer, p_start_date date, p_end_date date) OWNER TO postgres;

--
-- TOC entry 3749 (class 0 OID 0)
-- Dependencies: 271
-- Name: FUNCTION get_student_attendance_summary(p_student_id integer, p_start_date date, p_end_date date); Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON FUNCTION public.get_student_attendance_summary(p_student_id integer, p_start_date date, p_end_date date) IS 'Mendapatkan ringkasan kehadiran mahasiswa untuk rentang tanggal tertentu';


--
-- TOC entry 292 (class 1255 OID 74970)
-- Name: sp_delete_attendance(integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_delete_attendance(IN p_attendance_id integer, OUT p_success boolean, OUT p_message text)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_record_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Cek apakah record exists
    SELECT EXISTS(
        SELECT 1 FROM student_attendance WHERE id = p_attendance_id
    ) INTO v_record_exists;
    
    IF NOT v_record_exists THEN
        p_message := 'Data absensi tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Delete record
    DELETE FROM student_attendance WHERE id = p_attendance_id;
    
    -- Set success
    p_success := TRUE;
    p_message := 'Data absensi berhasil dihapus.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_delete_attendance: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.sp_delete_attendance(IN p_attendance_id integer, OUT p_success boolean, OUT p_message text) OWNER TO postgres;

--
-- TOC entry 299 (class 1255 OID 74971)
-- Name: sp_get_attendance_list(date, character varying, integer, integer, refcursor); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_get_attendance_list(IN p_date date DEFAULT NULL::date, IN p_student_name character varying DEFAULT NULL::character varying, IN p_limit integer DEFAULT 100, IN p_offset integer DEFAULT 0, INOUT result_cursor refcursor DEFAULT 'attendance_cursor'::refcursor)
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Open cursor dengan query berdasarkan filter
    OPEN result_cursor FOR
        SELECT 
            student_id,
            student_nim,
            student_name,
            attendance_date,
            checkin_time,
            checkout_time,
            duration_minutes,
            status
        FROM attendance_daily_view
        WHERE 
            (p_date IS NULL OR attendance_date = p_date)
            AND (p_student_name IS NULL OR 
                 student_name ILIKE '%' || p_student_name || '%' OR 
                 student_nim ILIKE '%' || p_student_name || '%')
        ORDER BY attendance_date DESC, checkin_time DESC
        LIMIT p_limit
        OFFSET p_offset;
END;
$$;


ALTER PROCEDURE public.sp_get_attendance_list(IN p_date date, IN p_student_name character varying, IN p_limit integer, IN p_offset integer, INOUT result_cursor refcursor) OWNER TO postgres;

--
-- TOC entry 294 (class 1255 OID 74972)
-- Name: sp_get_attendance_stats(date); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_get_attendance_stats(IN p_date date, OUT p_total_checkins integer, OUT p_total_checkouts integer, OUT p_unique_students integer, OUT p_incomplete_count integer, OUT p_complete_count integer)
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Set default date if null
    IF p_date IS NULL THEN
        p_date := CURRENT_DATE;
    END IF;
    -- Get statistics dari view attendance_stats_by_date
    SELECT 
        COALESCE(total_check_in, 0),
        COALESCE(total_check_out, 0),
        COALESCE(unique_students, 0),
        0, -- incomplete count (calculate separately)
        0  -- complete count (calculate separately)
    INTO 
        p_total_checkins,
        p_total_checkouts,
        p_unique_students,
        p_incomplete_count,
        p_complete_count
    FROM attendance_stats_by_date
    WHERE attendance_date = p_date;
    
    -- If no data found, set defaults
    IF NOT FOUND THEN
        p_total_checkins := 0;
        p_total_checkouts := 0;
        p_unique_students := 0;
        p_incomplete_count := 0;
        p_complete_count := 0;
    END IF;
    
    -- Calculate complete and incomplete counts
    SELECT 
        COUNT(*) FILTER (WHERE status = 'Lengkap'),
        COUNT(*) FILTER (WHERE status = 'Belum Pulang')
    INTO
        p_complete_count,
        p_incomplete_count
    FROM attendance_daily_view
    WHERE attendance_date = p_date;
    
EXCEPTION
    WHEN OTHERS THEN
        -- Set default values on error
        p_total_checkins := 0;
        p_total_checkouts := 0;
        p_unique_students := 0;
        p_incomplete_count := 0;
        p_complete_count := 0;
        RAISE NOTICE 'Error in sp_get_attendance_stats: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.sp_get_attendance_stats(IN p_date date, OUT p_total_checkins integer, OUT p_total_checkouts integer, OUT p_unique_students integer, OUT p_incomplete_count integer, OUT p_complete_count integer) OWNER TO postgres;

--
-- TOC entry 293 (class 1255 OID 74973)
-- Name: sp_get_student_attendance_summary(integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_get_student_attendance_summary(IN p_student_id integer, OUT p_total_days integer, OUT p_complete_days integer, OUT p_incomplete_days integer, OUT p_avg_duration_minutes numeric, OUT p_total_duration_hours numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- Get statistics dari view attendance_stats_by_student
    SELECT 
        COALESCE(total_days, 0),
        COALESCE(total_complete_days, 0),
        COALESCE(total_incomplete_days, 0),
        COALESCE(avg_duration_minutes, 0),
        COALESCE(total_duration_hours, 0)
    INTO 
        p_total_days,
        p_complete_days,
        p_incomplete_days,
        p_avg_duration_minutes,
        p_total_duration_hours
    FROM attendance_stats_by_student
    WHERE student_id = p_student_id;
    
    -- If no data found, set defaults
    IF NOT FOUND THEN
        p_total_days := 0;
        p_complete_days := 0;
        p_incomplete_days := 0;
        p_avg_duration_minutes := 0;
        p_total_duration_hours := 0;
    END IF;
    
EXCEPTION
    WHEN OTHERS THEN
        -- Set default values on error
        p_total_days := 0;
        p_complete_days := 0;
        p_incomplete_days := 0;
        p_avg_duration_minutes := 0;
        p_total_duration_hours := 0;
        RAISE NOTICE 'Error in sp_get_student_attendance_summary: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.sp_get_student_attendance_summary(IN p_student_id integer, OUT p_total_days integer, OUT p_complete_days integer, OUT p_incomplete_days integer, OUT p_avg_duration_minutes numeric, OUT p_total_duration_hours numeric) OWNER TO postgres;

--
-- TOC entry 296 (class 1255 OID 74968)
-- Name: sp_student_checkin(integer, character varying, character varying); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_student_checkin(IN p_student_id integer, OUT p_success boolean, OUT p_message text, IN p_ip_address character varying DEFAULT NULL::character varying, IN p_user_agent character varying DEFAULT NULL::character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_can_checkin BOOLEAN;
    v_student_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Validasi: Cek apakah student exists
    SELECT EXISTS(
        SELECT 1 FROM students WHERE id = p_student_id
    ) INTO v_student_exists;
    
    IF NOT v_student_exists THEN
        p_message := 'Mahasiswa tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Validasi: Gunakan function check_student_checkin_today
    SELECT check_student_checkin_today(p_student_id) INTO v_can_checkin;
    
    IF NOT v_can_checkin THEN
        p_message := 'Anda sudah melakukan absensi datang hari ini.';
        RETURN;
    END IF;
    
    -- Insert absensi datang
    INSERT INTO student_attendance (
        student_id,
        attendance_type,
        ip_address,
        user_agent
    ) VALUES (
        p_student_id,
        'datang',
        p_ip_address,
        p_user_agent
    );
    
    -- Set success
    p_success := TRUE;
    p_message := 'Absensi datang berhasil dicatat.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_student_checkin: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.sp_student_checkin(IN p_student_id integer, OUT p_success boolean, OUT p_message text, IN p_ip_address character varying, IN p_user_agent character varying) OWNER TO postgres;

--
-- TOC entry 297 (class 1255 OID 74969)
-- Name: sp_student_checkout(integer, character varying, character varying); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.sp_student_checkout(IN p_student_id integer, OUT p_success boolean, OUT p_message text, IN p_ip_address character varying DEFAULT NULL::character varying, IN p_user_agent character varying DEFAULT NULL::character varying)
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_can_checkout BOOLEAN;
    v_student_exists BOOLEAN;
BEGIN
    -- Set default values
    p_success := FALSE;
    p_message := '';
    
    -- Validasi: Cek apakah student exists
    SELECT EXISTS(
        SELECT 1 FROM students WHERE id = p_student_id
    ) INTO v_student_exists;
    
    IF NOT v_student_exists THEN
        p_message := 'Mahasiswa tidak ditemukan.';
        RETURN;
    END IF;
    
    -- Validasi: Gunakan function check_student_checkout_today
    SELECT check_student_checkout_today(p_student_id) INTO v_can_checkout;
    
    IF NOT v_can_checkout THEN
        p_message := 'Anda harus absen datang terlebih dahulu atau sudah absen pulang hari ini.';
        RETURN;
    END IF;
    
    -- Insert absensi pulang
    INSERT INTO student_attendance (
        student_id,
        attendance_type,
        ip_address,
        user_agent
    ) VALUES (
        p_student_id,
        'pulang',
        p_ip_address,
        p_user_agent
    );
    
    -- Set success
    p_success := TRUE;
    p_message := 'Absensi pulang berhasil dicatat.';
    
EXCEPTION
    WHEN OTHERS THEN
        p_success := FALSE;
        p_message := 'Error: ' || SQLERRM;
        RAISE NOTICE 'Error in sp_student_checkout: %', SQLERRM;
END;
$$;


ALTER PROCEDURE public.sp_student_checkout(IN p_student_id integer, OUT p_success boolean, OUT p_message text, IN p_ip_address character varying, IN p_user_agent character varying) OWNER TO postgres;

--
-- TOC entry 270 (class 1255 OID 66591)
-- Name: update_borrowing_timestamp(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_borrowing_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_borrowing_timestamp() OWNER TO postgres;

--
-- TOC entry 285 (class 1255 OID 66593)
-- Name: update_equipment_quantity_on_borrowing(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_equipment_quantity_on_borrowing() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_borrowing_status VARCHAR(20);
BEGIN
    -- Get borrowing status
    SELECT status INTO v_borrowing_status 
    FROM borrowings 
    WHERE id = NEW.borrowing_id;
    
    IF TG_OP = 'INSERT' THEN
        -- Decrease available quantity when borrowing is created
        IF v_borrowing_status = 'active' THEN
            UPDATE equipment 
            SET available_quantity = available_quantity - NEW.quantity
            WHERE id = NEW.equipment_id
            AND available_quantity >= NEW.quantity;
            
            IF NOT FOUND THEN
                RAISE EXCEPTION 'Insufficient equipment quantity available';
            END IF;
        END IF;
        RETURN NEW;
        
    ELSIF TG_OP = 'DELETE' THEN
        -- Increase available quantity when borrowing is deleted or returned
        UPDATE equipment 
        SET available_quantity = LEAST(available_quantity + OLD.quantity, quantity)
        WHERE id = OLD.equipment_id;
        RETURN OLD;
        
    END IF;
    
    RETURN NULL;
END;
$$;


ALTER FUNCTION public.update_equipment_quantity_on_borrowing() OWNER TO postgres;

--
-- TOC entry 286 (class 1255 OID 66595)
-- Name: update_equipment_quantity_on_return(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_equipment_quantity_on_return() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    -- If status changed from 'active' to 'returned'
    IF OLD.status = 'active' AND NEW.status = 'returned' AND NEW.type = 'equipment' THEN
        -- Restore equipment quantities
        UPDATE equipment e
        SET available_quantity = LEAST(
            e.available_quantity + be.quantity,
            e.quantity
        )
        FROM borrowing_equipment be
        WHERE be.borrowing_id = NEW.id
        AND be.equipment_id = e.id;
    END IF;
    
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_equipment_quantity_on_return() OWNER TO postgres;

--
-- TOC entry 295 (class 1255 OID 74988)
-- Name: update_guestbook_timestamp(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_guestbook_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_guestbook_timestamp() OWNER TO postgres;

--
-- TOC entry 298 (class 1255 OID 75018)
-- Name: update_site_settings_timestamp(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_site_settings_timestamp() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_site_settings_timestamp() OWNER TO postgres;

--
-- TOC entry 268 (class 1255 OID 66666)
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 260 (class 1259 OID 66738)
-- Name: about_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.about_settings (
    id integer NOT NULL,
    hero_title character varying(255),
    hero_subtitle character varying(255),
    vision_text text,
    mission_items jsonb,
    who_we_are_text text,
    quote_text text,
    focus_areas jsonb,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    version_name character varying(255) DEFAULT 'Default Version'::character varying,
    is_active boolean DEFAULT false,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.about_settings OWNER TO postgres;

--
-- TOC entry 259 (class 1259 OID 66737)
-- Name: about_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.about_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.about_settings_id_seq OWNER TO postgres;

--
-- TOC entry 3750 (class 0 OID 0)
-- Dependencies: 259
-- Name: about_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.about_settings_id_seq OWNED BY public.about_settings.id;


--
-- TOC entry 215 (class 1259 OID 41635)
-- Name: admin_users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin_users (
    id integer NOT NULL,
    username character varying(50) NOT NULL,
    password character varying(255) NOT NULL,
    email character varying(100) NOT NULL,
    full_name character varying(100),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    last_login timestamp without time zone
);


ALTER TABLE public.admin_users OWNER TO postgres;

--
-- TOC entry 214 (class 1259 OID 41634)
-- Name: admin_users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.admin_users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.admin_users_id_seq OWNER TO postgres;

--
-- TOC entry 3751 (class 0 OID 0)
-- Dependencies: 214
-- Name: admin_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_users_id_seq OWNED BY public.admin_users.id;


--
-- TOC entry 245 (class 1259 OID 66352)
-- Name: student_attendance; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.student_attendance (
    id integer NOT NULL,
    nama character varying(255),
    nim character varying(50),
    attendance_type character varying(10) NOT NULL,
    attendance_date date DEFAULT CURRENT_DATE NOT NULL,
    attendance_time time without time zone DEFAULT CURRENT_TIME NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ip_address character varying(45),
    user_agent text,
    student_id integer NOT NULL,
    CONSTRAINT student_attendance_attendance_type_check CHECK (((attendance_type)::text = ANY ((ARRAY['datang'::character varying, 'pulang'::character varying])::text[])))
);


ALTER TABLE public.student_attendance OWNER TO postgres;

--
-- TOC entry 3752 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN student_attendance.nama; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.student_attendance.nama IS 'DEPRECATED: Gunakan student_id untuk relasi';


--
-- TOC entry 3753 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN student_attendance.nim; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.student_attendance.nim IS 'DEPRECATED: Gunakan student_id untuk relasi';


--
-- TOC entry 3754 (class 0 OID 0)
-- Dependencies: 245
-- Name: COLUMN student_attendance.student_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.student_attendance.student_id IS 'Foreign key ke tabel students';


--
-- TOC entry 254 (class 1259 OID 66617)
-- Name: students; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.students (
    id integer NOT NULL,
    nim character varying(50) NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(20),
    program_study character varying(100),
    semester integer,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.students OWNER TO postgres;

--
-- TOC entry 3755 (class 0 OID 0)
-- Dependencies: 254
-- Name: TABLE students; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.students IS 'Master data mahasiswa';


--
-- TOC entry 3756 (class 0 OID 0)
-- Dependencies: 254
-- Name: COLUMN students.nim; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.students.nim IS 'Nomor Induk Mahasiswa (unique)';


--
-- TOC entry 3757 (class 0 OID 0)
-- Dependencies: 254
-- Name: COLUMN students.is_active; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.students.is_active IS 'Status aktif mahasiswa';


--
-- TOC entry 258 (class 1259 OID 66673)
-- Name: attendance_daily_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.attendance_daily_view AS
 SELECT s.id AS student_id,
    s.nim AS student_nim,
    s.name AS student_name,
    s.program_study,
    COALESCE(datang.attendance_date, pulang.attendance_date) AS attendance_date,
    datang.attendance_time AS checkin_time,
    pulang.attendance_time AS checkout_time,
        CASE
            WHEN ((datang.id IS NOT NULL) AND (pulang.id IS NOT NULL)) THEN 'complete'::text
            WHEN ((datang.id IS NOT NULL) AND (pulang.id IS NULL)) THEN 'incomplete'::text
            ELSE 'unknown'::text
        END AS status,
        CASE
            WHEN ((datang.attendance_time IS NOT NULL) AND (pulang.attendance_time IS NOT NULL)) THEN (EXTRACT(epoch FROM (pulang.attendance_time - datang.attendance_time)) / (60)::numeric)
            ELSE NULL::numeric
        END AS duration_minutes,
    datang.ip_address AS checkin_ip,
    pulang.ip_address AS checkout_ip,
    COALESCE(datang.created_at, pulang.created_at) AS created_at
   FROM ((public.students s
     LEFT JOIN public.student_attendance datang ON (((s.id = datang.student_id) AND ((datang.attendance_type)::text = 'datang'::text))))
     LEFT JOIN public.student_attendance pulang ON (((s.id = pulang.student_id) AND ((pulang.attendance_type)::text = 'pulang'::text) AND (pulang.attendance_date = datang.attendance_date))))
  WHERE ((datang.id IS NOT NULL) OR (pulang.id IS NOT NULL))
  ORDER BY COALESCE(datang.attendance_date, pulang.attendance_date) DESC, datang.attendance_time DESC;


ALTER TABLE public.attendance_daily_view OWNER TO postgres;

--
-- TOC entry 257 (class 1259 OID 66661)
-- Name: attendance_recent_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.attendance_recent_view AS
 SELECT sa.id,
    sa.student_id,
    s.nim,
    s.name AS student_name,
    s.program_study,
    sa.attendance_type,
    sa.attendance_date,
    sa.attendance_time,
    sa.ip_address,
    sa.created_at,
        CASE
            WHEN ((sa.attendance_type)::text = 'datang'::text) THEN 'Check In'::text
            WHEN ((sa.attendance_type)::text = 'pulang'::text) THEN 'Check Out'::text
            ELSE NULL::text
        END AS attendance_type_label
   FROM (public.student_attendance sa
     JOIN public.students s ON ((sa.student_id = s.id)))
  WHERE (s.is_active = true)
  ORDER BY sa.created_at DESC
 LIMIT 100;


ALTER TABLE public.attendance_recent_view OWNER TO postgres;

--
-- TOC entry 3758 (class 0 OID 0)
-- Dependencies: 257
-- Name: VIEW attendance_recent_view; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON VIEW public.attendance_recent_view IS 'View untuk menampilkan 100 record absensi terbaru dengan info mahasiswa';


--
-- TOC entry 256 (class 1259 OID 66656)
-- Name: attendance_stats_by_date; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.attendance_stats_by_date AS
 SELECT sa.attendance_date,
    to_char((sa.attendance_date)::timestamp with time zone, 'Day'::text) AS day_name,
    count(DISTINCT
        CASE
            WHEN ((sa.attendance_type)::text = 'datang'::text) THEN sa.student_id
            ELSE NULL::integer
        END) AS total_check_in,
    count(DISTINCT
        CASE
            WHEN ((sa.attendance_type)::text = 'pulang'::text) THEN sa.student_id
            ELSE NULL::integer
        END) AS total_check_out,
    count(DISTINCT sa.student_id) AS unique_students,
    array_agg(DISTINCT s.name ORDER BY s.name) FILTER (WHERE ((sa.attendance_type)::text = 'datang'::text)) AS students_checked_in
   FROM (public.student_attendance sa
     JOIN public.students s ON ((sa.student_id = s.id)))
  GROUP BY sa.attendance_date
  ORDER BY sa.attendance_date DESC;


ALTER TABLE public.attendance_stats_by_date OWNER TO postgres;

--
-- TOC entry 3759 (class 0 OID 0)
-- Dependencies: 256
-- Name: VIEW attendance_stats_by_date; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON VIEW public.attendance_stats_by_date IS 'View untuk statistik kehadiran per tanggal';


--
-- TOC entry 255 (class 1259 OID 66651)
-- Name: attendance_stats_by_student; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.attendance_stats_by_student AS
 SELECT s.id AS student_id,
    s.nim,
    s.name AS student_name,
    s.program_study,
    count(DISTINCT
        CASE
            WHEN ((sa.attendance_type)::text = 'datang'::text) THEN sa.attendance_date
            ELSE NULL::date
        END) AS total_check_in,
    count(DISTINCT
        CASE
            WHEN ((sa.attendance_type)::text = 'pulang'::text) THEN sa.attendance_date
            ELSE NULL::date
        END) AS total_check_out,
    count(DISTINCT sa.attendance_date) AS total_attendance_days,
    max(sa.attendance_date) AS last_attendance_date,
    min(sa.attendance_date) AS first_attendance_date
   FROM (public.students s
     LEFT JOIN public.student_attendance sa ON ((s.id = sa.student_id)))
  WHERE (s.is_active = true)
  GROUP BY s.id, s.nim, s.name, s.program_study
  ORDER BY (count(DISTINCT sa.attendance_date)) DESC, s.name;


ALTER TABLE public.attendance_stats_by_student OWNER TO postgres;

--
-- TOC entry 3760 (class 0 OID 0)
-- Dependencies: 255
-- Name: VIEW attendance_stats_by_student; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON VIEW public.attendance_stats_by_student IS 'View untuk statistik kehadiran per mahasiswa';


--
-- TOC entry 251 (class 1259 OID 66538)
-- Name: borrowing_equipment; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.borrowing_equipment (
    id integer NOT NULL,
    borrowing_id integer NOT NULL,
    equipment_id integer NOT NULL,
    quantity integer DEFAULT 1,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_quantity_positive CHECK ((quantity > 0))
);


ALTER TABLE public.borrowing_equipment OWNER TO postgres;

--
-- TOC entry 3761 (class 0 OID 0)
-- Dependencies: 251
-- Name: TABLE borrowing_equipment; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.borrowing_equipment IS 'Junction table for many-to-many relationship between borrowings and equipment';


--
-- TOC entry 3762 (class 0 OID 0)
-- Dependencies: 251
-- Name: COLUMN borrowing_equipment.quantity; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.borrowing_equipment.quantity IS 'Number of units borrowed for this equipment item';


--
-- TOC entry 246 (class 1259 OID 66369)
-- Name: borrowings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.borrowings (
    id integer NOT NULL,
    borrower_name character varying(100) NOT NULL,
    occupation character varying(50) NOT NULL,
    type character varying(20) NOT NULL,
    item_name text,
    borrow_date timestamp without time zone NOT NULL,
    return_date timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    status character varying(20) DEFAULT 'active'::character varying,
    item_id integer,
    item_type character varying(20),
    notes text,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    room_name character varying(100) DEFAULT 'Lab Inlet'::character varying,
    borrower_email character varying(255),
    approval_status character varying(20) DEFAULT 'pending'::character varying,
    admin_notes text,
    CONSTRAINT borrowings_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'returned'::character varying, 'cancelled'::character varying])::text[]))),
    CONSTRAINT borrowings_type_check CHECK (((type)::text = ANY (ARRAY[('equipment'::character varying)::text, ('room'::character varying)::text]))),
    CONSTRAINT borrowings_type_check_new CHECK (((type)::text = ANY ((ARRAY['equipment'::character varying, 'room'::character varying])::text[])))
);


ALTER TABLE public.borrowings OWNER TO postgres;

--
-- TOC entry 3764 (class 0 OID 0)
-- Dependencies: 246
-- Name: COLUMN borrowings.item_name; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.borrowings.item_name IS 'DEPRECATED: Use borrowing_equipment junction table or room_id instead';


--
-- TOC entry 3765 (class 0 OID 0)
-- Dependencies: 246
-- Name: COLUMN borrowings.item_id; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.borrowings.item_id IS 'DEPRECATED: Use borrowing_equipment junction table or room_id instead';


--
-- TOC entry 3766 (class 0 OID 0)
-- Dependencies: 246
-- Name: COLUMN borrowings.item_type; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.borrowings.item_type IS 'DEPRECATED: Use type column instead';


--
-- TOC entry 249 (class 1259 OID 66381)
-- Name: equipment; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.equipment (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    quantity integer DEFAULT 1,
    available_quantity integer DEFAULT 1,
    display_order integer DEFAULT 999,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.equipment OWNER TO postgres;

--
-- TOC entry 267 (class 1259 OID 75049)
-- Name: borrowing_details_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.borrowing_details_view AS
 SELECT b.id AS borrowing_id,
    b.borrower_name,
    b.borrower_email,
    b.occupation,
    b.type AS borrowing_type,
    b.status,
    b.approval_status,
    b.admin_notes,
    b.borrow_date,
    b.return_date,
    b.room_name,
    b.notes,
    b.created_at,
    b.updated_at,
        CASE
            WHEN ((b.type)::text = 'equipment'::text) THEN ( SELECT string_agg((e.name)::text, ', '::text) AS string_agg
               FROM (public.borrowing_equipment be
                 JOIN public.equipment e ON ((be.equipment_id = e.id)))
              WHERE (be.borrowing_id = b.id))
            ELSE NULL::text
        END AS equipment_names,
        CASE
            WHEN ((b.type)::text = 'equipment'::text) THEN ( SELECT count(*) AS count
               FROM public.borrowing_equipment be
              WHERE (be.borrowing_id = b.id))
            ELSE NULL::bigint
        END AS total_items
   FROM public.borrowings b
  ORDER BY b.created_at DESC;


ALTER TABLE public.borrowing_details_view OWNER TO postgres;

--
-- TOC entry 250 (class 1259 OID 66537)
-- Name: borrowing_equipment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.borrowing_equipment_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.borrowing_equipment_id_seq OWNER TO postgres;

--
-- TOC entry 3767 (class 0 OID 0)
-- Dependencies: 250
-- Name: borrowing_equipment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.borrowing_equipment_id_seq OWNED BY public.borrowing_equipment.id;


--
-- TOC entry 261 (class 1259 OID 74936)
-- Name: borrowing_history_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.borrowing_history_view AS
 SELECT b.id AS borrowing_id,
    b.borrower_name,
    b.occupation,
    b.type AS borrowing_type,
    b.borrow_date,
    b.return_date,
    b.room_name,
    b.notes,
    string_agg(DISTINCT (e.name)::text, ', '::text) AS equipment_names,
    count(DISTINCT be.equipment_id) AS total_items,
    (EXTRACT(epoch FROM (b.return_date - b.borrow_date)) / (3600)::numeric) AS duration_hours
   FROM ((public.borrowings b
     LEFT JOIN public.borrowing_equipment be ON ((b.id = be.borrowing_id)))
     LEFT JOIN public.equipment e ON ((be.equipment_id = e.id)))
  WHERE ((b.status)::text = 'returned'::text)
  GROUP BY b.id, b.borrower_name, b.occupation, b.type, b.borrow_date, b.return_date, b.room_name, b.notes
  ORDER BY b.return_date DESC;


ALTER TABLE public.borrowing_history_view OWNER TO postgres;

--
-- TOC entry 247 (class 1259 OID 66376)
-- Name: borrowings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.borrowings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.borrowings_id_seq OWNER TO postgres;

--
-- TOC entry 3769 (class 0 OID 0)
-- Dependencies: 247
-- Name: borrowings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.borrowings_id_seq OWNED BY public.borrowings.id;


--
-- TOC entry 219 (class 1259 OID 41662)
-- Name: card_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.card_data (
    id integer NOT NULL,
    icon character varying(100) NOT NULL,
    value character varying(50) NOT NULL,
    label character varying(100) NOT NULL,
    display_order integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.card_data OWNER TO postgres;

--
-- TOC entry 218 (class 1259 OID 41661)
-- Name: card_data_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.card_data_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.card_data_id_seq OWNER TO postgres;

--
-- TOC entry 3770 (class 0 OID 0)
-- Dependencies: 218
-- Name: card_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.card_data_id_seq OWNED BY public.card_data.id;


--
-- TOC entry 217 (class 1259 OID 41649)
-- Name: carousel_banners; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.carousel_banners (
    id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text,
    image character varying(255) NOT NULL,
    button_text character varying(50),
    button_link character varying(255),
    display_order integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.carousel_banners OWNER TO postgres;

--
-- TOC entry 216 (class 1259 OID 41648)
-- Name: carousel_banners_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.carousel_banners_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.carousel_banners_id_seq OWNER TO postgres;

--
-- TOC entry 3771 (class 0 OID 0)
-- Dependencies: 216
-- Name: carousel_banners_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.carousel_banners_id_seq OWNED BY public.carousel_banners.id;


--
-- TOC entry 252 (class 1259 OID 66574)
-- Name: equipment_availability_view; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.equipment_availability_view AS
 SELECT e.id AS equipment_id,
    e.name AS equipment_name,
    e.description,
    e.quantity AS total_quantity,
    e.available_quantity AS current_available,
    e.display_order,
    e.is_active,
    COALESCE(( SELECT sum(be.quantity) AS sum
           FROM (public.borrowing_equipment be
             JOIN public.borrowings b ON ((be.borrowing_id = b.id)))
          WHERE ((be.equipment_id = e.id) AND ((b.status)::text = 'active'::text))), (0)::bigint) AS currently_borrowed,
    (e.quantity - COALESCE(( SELECT sum(be.quantity) AS sum
           FROM (public.borrowing_equipment be
             JOIN public.borrowings b ON ((be.borrowing_id = b.id)))
          WHERE ((be.equipment_id = e.id) AND ((b.status)::text = 'active'::text))), (0)::bigint)) AS expected_available,
        CASE
            WHEN (e.is_active = false) THEN 'inactive'::text
            WHEN (e.available_quantity <= 0) THEN 'unavailable'::text
            WHEN ((e.available_quantity)::numeric <= ((e.quantity)::numeric * 0.2)) THEN 'low_stock'::text
            ELSE 'available'::text
        END AS availability_status,
    ( SELECT count(DISTINCT b.id) AS count
           FROM (public.borrowing_equipment be
             JOIN public.borrowings b ON ((be.borrowing_id = b.id)))
          WHERE (be.equipment_id = e.id)) AS total_borrowing_count,
    ( SELECT max(b.return_date) AS max
           FROM (public.borrowing_equipment be
             JOIN public.borrowings b ON ((be.borrowing_id = b.id)))
          WHERE ((be.equipment_id = e.id) AND ((b.status)::text = 'active'::text))) AS next_return_date
   FROM public.equipment e;


ALTER TABLE public.equipment_availability_view OWNER TO postgres;

--
-- TOC entry 3772 (class 0 OID 0)
-- Dependencies: 252
-- Name: VIEW equipment_availability_view; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON VIEW public.equipment_availability_view IS 'Real-time equipment availability with borrowing statistics';


--
-- TOC entry 248 (class 1259 OID 66380)
-- Name: equipment_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.equipment_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.equipment_id_seq OWNER TO postgres;

--
-- TOC entry 3774 (class 0 OID 0)
-- Dependencies: 248
-- Name: equipment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.equipment_id_seq OWNED BY public.equipment.id;


--
-- TOC entry 229 (class 1259 OID 41724)
-- Name: gallery; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.gallery (
    id integer NOT NULL,
    image character varying(255) NOT NULL,
    caption character varying(255),
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.gallery OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 41723)
-- Name: gallery_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.gallery_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.gallery_id_seq OWNER TO postgres;

--
-- TOC entry 3775 (class 0 OID 0)
-- Dependencies: 228
-- Name: gallery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_id_seq OWNED BY public.gallery.id;


--
-- TOC entry 263 (class 1259 OID 74975)
-- Name: guestbook; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.guestbook (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    institution character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(50) NOT NULL,
    message text NOT NULL,
    ip_address character varying(45),
    user_agent text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.guestbook OWNER TO postgres;

--
-- TOC entry 3776 (class 0 OID 0)
-- Dependencies: 263
-- Name: TABLE guestbook; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.guestbook IS 'Tabel untuk menyimpan data buku tamu pengunjung website Lab Inlet';


--
-- TOC entry 3777 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.name; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.name IS 'Nama lengkap pengunjung';


--
-- TOC entry 3778 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.institution; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.institution IS 'Institusi/organisasi pengunjung';


--
-- TOC entry 3779 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.email; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.email IS 'Email pengunjung';


--
-- TOC entry 3780 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.phone; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.phone IS 'Nomor telepon pengunjung';


--
-- TOC entry 3781 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.message; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.message IS 'Pesan dari pengunjung';


--
-- TOC entry 3782 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.ip_address; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.ip_address IS 'IP address pengunjung saat submit';


--
-- TOC entry 3783 (class 0 OID 0)
-- Dependencies: 263
-- Name: COLUMN guestbook.user_agent; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.guestbook.user_agent IS 'Browser user agent pengunjung';


--
-- TOC entry 262 (class 1259 OID 74974)
-- Name: guestbook_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.guestbook_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.guestbook_id_seq OWNER TO postgres;

--
-- TOC entry 3784 (class 0 OID 0)
-- Dependencies: 262
-- Name: guestbook_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.guestbook_id_seq OWNED BY public.guestbook.id;


--
-- TOC entry 264 (class 1259 OID 74990)
-- Name: guestbook_stats; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.guestbook_stats AS
 SELECT count(*) AS total_entries,
    count(DISTINCT guestbook.email) AS unique_visitors,
    count(DISTINCT guestbook.institution) AS unique_institutions,
    date(min(guestbook.created_at)) AS first_entry_date,
    date(max(guestbook.created_at)) AS last_entry_date
   FROM public.guestbook;


ALTER TABLE public.guestbook_stats OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 58176)
-- Name: lecturer_education; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lecturer_education (
    id integer NOT NULL,
    team_member_id integer NOT NULL,
    institution character varying(255) NOT NULL,
    degree character varying(100) NOT NULL,
    field_of_study character varying(255),
    start_date date,
    end_date date,
    description text,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.lecturer_education OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 58175)
-- Name: lecturer_education_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lecturer_education_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lecturer_education_id_seq OWNER TO postgres;

--
-- TOC entry 3785 (class 0 OID 0)
-- Dependencies: 236
-- Name: lecturer_education_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_education_id_seq OWNED BY public.lecturer_education.id;


--
-- TOC entry 241 (class 1259 OID 58205)
-- Name: lecturer_interests; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lecturer_interests (
    id integer NOT NULL,
    team_member_id integer NOT NULL,
    interest_text text NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.lecturer_interests OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 58204)
-- Name: lecturer_interests_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lecturer_interests_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lecturer_interests_id_seq OWNER TO postgres;

--
-- TOC entry 3786 (class 0 OID 0)
-- Dependencies: 240
-- Name: lecturer_interests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_interests_id_seq OWNED BY public.lecturer_interests.id;


--
-- TOC entry 235 (class 1259 OID 58160)
-- Name: lecturer_projects; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lecturer_projects (
    id integer NOT NULL,
    team_member_id integer NOT NULL,
    title character varying(255) NOT NULL,
    category character varying(100),
    description text,
    start_date date,
    end_date date,
    status character varying(50) DEFAULT 'ongoing'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.lecturer_projects OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 58159)
-- Name: lecturer_projects_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lecturer_projects_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lecturer_projects_id_seq OWNER TO postgres;

--
-- TOC entry 3787 (class 0 OID 0)
-- Dependencies: 234
-- Name: lecturer_projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_projects_id_seq OWNED BY public.lecturer_projects.id;


--
-- TOC entry 243 (class 1259 OID 58220)
-- Name: lecturer_publications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lecturer_publications (
    id integer NOT NULL,
    team_member_id integer NOT NULL,
    title character varying(500) NOT NULL,
    year integer,
    url character varying(500),
    type character varying(50) DEFAULT 'article'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.lecturer_publications OWNER TO postgres;

--
-- TOC entry 242 (class 1259 OID 58219)
-- Name: lecturer_publications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lecturer_publications_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lecturer_publications_id_seq OWNER TO postgres;

--
-- TOC entry 3788 (class 0 OID 0)
-- Dependencies: 242
-- Name: lecturer_publications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_publications_id_seq OWNED BY public.lecturer_publications.id;


--
-- TOC entry 239 (class 1259 OID 58191)
-- Name: lecturer_skills; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.lecturer_skills (
    id integer NOT NULL,
    team_member_id integer NOT NULL,
    category character varying(100) NOT NULL,
    skill_name character varying(100) NOT NULL,
    skill_icon character varying(255),
    proficiency integer DEFAULT 3,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.lecturer_skills OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 58190)
-- Name: lecturer_skills_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.lecturer_skills_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.lecturer_skills_id_seq OWNER TO postgres;

--
-- TOC entry 3789 (class 0 OID 0)
-- Dependencies: 238
-- Name: lecturer_skills_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_skills_id_seq OWNED BY public.lecturer_skills.id;


--
-- TOC entry 227 (class 1259 OID 41711)
-- Name: news; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.news (
    id integer NOT NULL,
    title character varying(200) NOT NULL,
    content text NOT NULL,
    image character varying(255) NOT NULL,
    author character varying(100),
    published_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.news OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 41710)
-- Name: news_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.news_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.news_id_seq OWNER TO postgres;

--
-- TOC entry 3790 (class 0 OID 0)
-- Dependencies: 226
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.news_id_seq OWNED BY public.news.id;


--
-- TOC entry 223 (class 1259 OID 41685)
-- Name: partners; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.partners (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    logo character varying(255) NOT NULL,
    website character varying(255),
    display_order integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.partners OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 41684)
-- Name: partners_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.partners_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.partners_id_seq OWNER TO postgres;

--
-- TOC entry 3791 (class 0 OID 0)
-- Dependencies: 222
-- Name: partners_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.partners_id_seq OWNED BY public.partners.id;


--
-- TOC entry 221 (class 1259 OID 41673)
-- Name: products; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products (
    id integer NOT NULL,
    name character varying(200) NOT NULL,
    description text,
    image character varying(255) NOT NULL,
    full_description text,
    features text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    year integer DEFAULT EXTRACT(year FROM CURRENT_DATE) NOT NULL,
    demo_link character varying(500) DEFAULT NULL::character varying
);


ALTER TABLE public.products OWNER TO postgres;

--
-- TOC entry 3792 (class 0 OID 0)
-- Dependencies: 221
-- Name: COLUMN products.year; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.products.year IS 'Tahun pembuatan produk';


--
-- TOC entry 3793 (class 0 OID 0)
-- Dependencies: 221
-- Name: COLUMN products.demo_link; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.products.demo_link IS 'Link untuk demo produk (opsional)';


--
-- TOC entry 220 (class 1259 OID 41672)
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.products_id_seq OWNER TO postgres;

--
-- TOC entry 3794 (class 0 OID 0)
-- Dependencies: 220
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- TOC entry 231 (class 1259 OID 41736)
-- Name: research_activities; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.research_activities (
    id integer NOT NULL,
    title character varying(200) NOT NULL,
    description text NOT NULL,
    image character varying(255) NOT NULL,
    full_description text,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    youtube_url character varying(500) DEFAULT NULL::character varying
);


ALTER TABLE public.research_activities OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 41735)
-- Name: research_activities_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.research_activities_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.research_activities_id_seq OWNER TO postgres;

--
-- TOC entry 3795 (class 0 OID 0)
-- Dependencies: 230
-- Name: research_activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.research_activities_id_seq OWNED BY public.research_activities.id;


--
-- TOC entry 266 (class 1259 OID 75020)
-- Name: site_settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.site_settings (
    id integer NOT NULL,
    logo_path character varying(255) DEFAULT 'assets/images/inlet_logo_only.png'::character varying,
    logo_alt character varying(100) DEFAULT 'INLET Logo'::character varying,
    footer_about_title character varying(100) DEFAULT 'INLET'::character varying,
    footer_about_text text DEFAULT 'Innovation and Laboratory of Electrical Techniques - Pusat inovasi dan laboratorium untuk pengembangan teknik elektro dan teknologi terkini.'::text,
    contact_email character varying(100) DEFAULT 'inlet@polinema.ac.id'::character varying,
    contact_phone character varying(50) DEFAULT '+62 341 404424'::character varying,
    contact_address text DEFAULT 'Jl. Soekarno Hatta No.9, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141'::text,
    social_facebook character varying(255) DEFAULT 'https://facebook.com/inlet.polinema'::character varying,
    social_instagram character varying(255) DEFAULT 'https://instagram.com/inlet.polinema'::character varying,
    social_youtube character varying(255) DEFAULT 'https://youtube.com/@inlet.polinema'::character varying,
    social_linkedin character varying(255) DEFAULT NULL::character varying,
    social_twitter character varying(255) DEFAULT NULL::character varying,
    quick_links json DEFAULT '[]'::json,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by integer,
    footer_copyright character varying(255) DEFAULT 'Â© 2025 Information and Learning Engineering Technology Laboratory. All rights reserved.'::character varying,
    borrowing_notification_email character varying(255) DEFAULT 'inlet@polinema.ac.id'::character varying,
    CONSTRAINT single_row_constraint CHECK ((id = 1))
);


ALTER TABLE public.site_settings OWNER TO postgres;

--
-- TOC entry 3796 (class 0 OID 0)
-- Dependencies: 266
-- Name: TABLE site_settings; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON TABLE public.site_settings IS 'Store dynamic site configuration for logo, footer, and contact information';


--
-- TOC entry 3797 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN site_settings.logo_path; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.site_settings.logo_path IS 'Path to logo image file';


--
-- TOC entry 3798 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN site_settings.footer_about_text; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.site_settings.footer_about_text IS 'About text displayed in footer';


--
-- TOC entry 3799 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN site_settings.quick_links; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.site_settings.quick_links IS 'JSON array of quick links for footer';


--
-- TOC entry 3800 (class 0 OID 0)
-- Dependencies: 266
-- Name: COLUMN site_settings.footer_copyright; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.site_settings.footer_copyright IS 'Copyright text displayed in footer';


--
-- TOC entry 265 (class 1259 OID 75019)
-- Name: site_settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.site_settings_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.site_settings_id_seq OWNER TO postgres;

--
-- TOC entry 3801 (class 0 OID 0)
-- Dependencies: 265
-- Name: site_settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.site_settings_id_seq OWNED BY public.site_settings.id;


--
-- TOC entry 244 (class 1259 OID 66351)
-- Name: student_attendance_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.student_attendance_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.student_attendance_id_seq OWNER TO postgres;

--
-- TOC entry 3802 (class 0 OID 0)
-- Dependencies: 244
-- Name: student_attendance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.student_attendance_id_seq OWNED BY public.student_attendance.id;


--
-- TOC entry 253 (class 1259 OID 66616)
-- Name: students_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.students_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.students_id_seq OWNER TO postgres;

--
-- TOC entry 3803 (class 0 OID 0)
-- Dependencies: 253
-- Name: students_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.students_id_seq OWNED BY public.students.id;


--
-- TOC entry 225 (class 1259 OID 41698)
-- Name: team_members; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.team_members (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    "position" character varying(100) NOT NULL,
    photo character varying(255) NOT NULL,
    bio text,
    email character varying(100),
    linkedin character varying(255),
    display_order integer DEFAULT 0,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    address text,
    phone character varying(50),
    linkedin_url character varying(255),
    google_scholar_url character varying(255),
    researchgate_url character varying(255),
    orcid_url character varying(255)
);


ALTER TABLE public.team_members OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 41697)
-- Name: team_members_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.team_members_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.team_members_id_seq OWNER TO postgres;

--
-- TOC entry 3804 (class 0 OID 0)
-- Dependencies: 224
-- Name: team_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.team_members_id_seq OWNED BY public.team_members.id;


--
-- TOC entry 233 (class 1259 OID 58149)
-- Name: visitors; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.visitors (
    id integer NOT NULL,
    ip_address character varying(45) NOT NULL,
    user_agent text,
    country character varying(100),
    country_code character varying(5),
    page_url character varying(255),
    visited_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.visitors OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 58148)
-- Name: visitors_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.visitors_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.visitors_id_seq OWNER TO postgres;

--
-- TOC entry 3805 (class 0 OID 0)
-- Dependencies: 232
-- Name: visitors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitors_id_seq OWNED BY public.visitors.id;


--
-- TOC entry 3417 (class 2604 OID 66741)
-- Name: about_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.about_settings ALTER COLUMN id SET DEFAULT nextval('public.about_settings_id_seq'::regclass);


--
-- TOC entry 3336 (class 2604 OID 41638)
-- Name: admin_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users ALTER COLUMN id SET DEFAULT nextval('public.admin_users_id_seq'::regclass);


--
-- TOC entry 3410 (class 2604 OID 66541)
-- Name: borrowing_equipment id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowing_equipment ALTER COLUMN id SET DEFAULT nextval('public.borrowing_equipment_id_seq'::regclass);


--
-- TOC entry 3397 (class 2604 OID 66377)
-- Name: borrowings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings ALTER COLUMN id SET DEFAULT nextval('public.borrowings_id_seq'::regclass);


--
-- TOC entry 3343 (class 2604 OID 41665)
-- Name: card_data id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.card_data ALTER COLUMN id SET DEFAULT nextval('public.card_data_id_seq'::regclass);


--
-- TOC entry 3338 (class 2604 OID 41652)
-- Name: carousel_banners id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners ALTER COLUMN id SET DEFAULT nextval('public.carousel_banners_id_seq'::regclass);


--
-- TOC entry 3403 (class 2604 OID 66384)
-- Name: equipment id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment ALTER COLUMN id SET DEFAULT nextval('public.equipment_id_seq'::regclass);


--
-- TOC entry 3369 (class 2604 OID 41727)
-- Name: gallery id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN id SET DEFAULT nextval('public.gallery_id_seq'::regclass);


--
-- TOC entry 3422 (class 2604 OID 74978)
-- Name: guestbook id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guestbook ALTER COLUMN id SET DEFAULT nextval('public.guestbook_id_seq'::regclass);


--
-- TOC entry 3383 (class 2604 OID 58179)
-- Name: lecturer_education id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education ALTER COLUMN id SET DEFAULT nextval('public.lecturer_education_id_seq'::regclass);


--
-- TOC entry 3388 (class 2604 OID 58208)
-- Name: lecturer_interests id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests ALTER COLUMN id SET DEFAULT nextval('public.lecturer_interests_id_seq'::regclass);


--
-- TOC entry 3380 (class 2604 OID 58163)
-- Name: lecturer_projects id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects ALTER COLUMN id SET DEFAULT nextval('public.lecturer_projects_id_seq'::regclass);


--
-- TOC entry 3390 (class 2604 OID 58223)
-- Name: lecturer_publications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications ALTER COLUMN id SET DEFAULT nextval('public.lecturer_publications_id_seq'::regclass);


--
-- TOC entry 3385 (class 2604 OID 58194)
-- Name: lecturer_skills id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills ALTER COLUMN id SET DEFAULT nextval('public.lecturer_skills_id_seq'::regclass);


--
-- TOC entry 3364 (class 2604 OID 41714)
-- Name: news id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.news ALTER COLUMN id SET DEFAULT nextval('public.news_id_seq'::regclass);


--
-- TOC entry 3354 (class 2604 OID 41688)
-- Name: partners id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners ALTER COLUMN id SET DEFAULT nextval('public.partners_id_seq'::regclass);


--
-- TOC entry 3348 (class 2604 OID 41676)
-- Name: products id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- TOC entry 3373 (class 2604 OID 41739)
-- Name: research_activities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_activities ALTER COLUMN id SET DEFAULT nextval('public.research_activities_id_seq'::regclass);


--
-- TOC entry 3425 (class 2604 OID 75023)
-- Name: site_settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_settings ALTER COLUMN id SET DEFAULT nextval('public.site_settings_id_seq'::regclass);


--
-- TOC entry 3393 (class 2604 OID 66355)
-- Name: student_attendance id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance ALTER COLUMN id SET DEFAULT nextval('public.student_attendance_id_seq'::regclass);


--
-- TOC entry 3413 (class 2604 OID 66620)
-- Name: students id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.students ALTER COLUMN id SET DEFAULT nextval('public.students_id_seq'::regclass);


--
-- TOC entry 3359 (class 2604 OID 41701)
-- Name: team_members id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members ALTER COLUMN id SET DEFAULT nextval('public.team_members_id_seq'::regclass);


--
-- TOC entry 3378 (class 2604 OID 58152)
-- Name: visitors id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitors ALTER COLUMN id SET DEFAULT nextval('public.visitors_id_seq'::regclass);


--
-- TOC entry 3738 (class 0 OID 66738)
-- Dependencies: 260
-- Data for Name: about_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.about_settings (id, hero_title, hero_subtitle, vision_text, mission_items, who_we_are_text, quote_text, focus_areas, updated_at, version_name, is_active, created_at) FROM stdin;
2	About InLet	Information and Learning Engineering Technology	Vision example	["Develop innovative learning technologies and information systems", "Develop innovative learning technologies and information systems"]	We are the most inovative lab ever	The greatest lab has ever lived	[{"icon": "fas fa-graduation-cap", "color": "blue", "title": "Educational Technology", "description": "Developing innovative learning platforms and tools"}]	2025-11-23 20:02:48.165809	About versi 2	f	2025-11-23 20:02:48.165809
1	About InLet	Information and Learning Engineering Technology	To become a leading research and development center in information and learning engineering technology, driving innovation and excellence in education technology solutions that transform the way people learn and interact with information.	["Develop innovative learning technologies and information systems", "Conduct cutting-edge research in educational technology", "Foster collaboration with industry and academic partners", "Create impactful solutions for modern learning challenges"]	InLet (Information and Learning Engineering Technology) is a dynamic research laboratory dedicated to advancing the field of educational technology and information systems. Founded with the vision of transforming learning experiences through technology, we bring together researchers, developers, and educators to create innovative solutions.\r\n\r\nOur team consists of experienced professionals with diverse backgrounds in computer science, education, and engineering. We work on various projects ranging from learning management systems to artificial intelligence applications in education.	Through our research activities, product development, and industry partnerships, we aim to make a significant impact on how technology is used in education and information management.	[{"icon": "fas fa-graduation-cap", "color": "blue", "title": "Educational Technology", "description": "Developing innovative learning platforms and tools"}, {"icon": "fas fa-robot", "color": "green", "title": "Artificial Intelligence", "description": "AI applications in education and information systems"}, {"icon": "fas fa-database", "color": "purple", "title": "Information Systems", "description": "Building robust and scalable information management solutions"}, {"icon": "fas fa-mobile-alt", "color": "orange", "title": "Mobile Applications", "description": "Creating mobile-first learning experiences"}]	2025-11-23 19:55:59.682455	Default Version	t	2025-11-23 19:51:07.074682
\.


--
-- TOC entry 3698 (class 0 OID 41635)
-- Dependencies: 215
-- Data for Name: admin_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin_users (id, username, password, email, full_name, created_at, last_login) FROM stdin;
1	admin_inlet	$2y$10$2C6uvj28j8awouiuB6rn3.dRDbor0hXAX.bcTCct1TjHgYT1lE2Pu	admin@inlet.com	Administrator	2025-11-02 22:58:40.299109	2025-12-08 13:41:12.677546
\.


--
-- TOC entry 3734 (class 0 OID 66538)
-- Dependencies: 251
-- Data for Name: borrowing_equipment; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.borrowing_equipment (id, borrowing_id, equipment_id, quantity, created_at) FROM stdin;
24	35	9	1	2025-12-09 21:15:03.641759
\.


--
-- TOC entry 3729 (class 0 OID 66369)
-- Dependencies: 246
-- Data for Name: borrowings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.borrowings (id, borrower_name, occupation, type, item_name, borrow_date, return_date, created_at, status, item_id, item_type, notes, updated_at, room_name, borrower_email, approval_status, admin_notes) FROM stdin;
33	Achmad Nabil Afgareza	Mahasiswa	room	\N	2025-12-03 08:00:00	2025-12-04 16:00:00	2025-12-03 08:44:53.717417	returned	\N	\N	Peminjaman Lab	2025-12-09 21:03:03.456881	Lab Inlet	nabilafgareza12@gmail.com	approved	Silahkan
34	Achmad Nabil Afgareza	Mahasiswa	room	\N	2025-12-09 12:00:00	2025-12-10 12:00:00	2025-12-09 20:53:26.731141	returned	\N	\N	Makan-Makan	2025-12-09 21:37:40.269176	Lab Inlet	nabilafgareza12@gmail.com	rejected	Maaf tidak bisa
35	Pak Banni	Dosen	equipment	\N	2025-12-09 12:00:00	2025-12-10 12:00:00	2025-12-09 21:15:03.641759	returned	\N	\N	\N	2025-12-09 21:47:13.84316	Lab Inlet	nabilafgareza12@gmail.com	approved	Monggooo
\.


--
-- TOC entry 3702 (class 0 OID 41662)
-- Dependencies: 219
-- Data for Name: card_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.card_data (id, icon, value, label, display_order, is_active, created_at, updated_at) FROM stdin;
1	fas fa-newspaper	50+	Related Articles	1	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
2	fas fa-users	11	Team Members	2	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
3	fas fa-project-diagram	5	Prototype	3	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	fas fa-address-card	50+	Students involved	4	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
\.


--
-- TOC entry 3700 (class 0 OID 41649)
-- Dependencies: 217
-- Data for Name: carousel_banners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.carousel_banners (id, title, description, image, button_text, button_link, display_order, is_active, created_at, updated_at) FROM stdin;
1	Learning Engineering Technology  Research Group	Find amazing Learning application tailored for you. Handy connects you with amazing Learning Engineering professionals.	692fd5e741d32.jpg	Learn More	index.php?page=about	1	t	2025-11-02 22:58:40.299109	2025-12-03 13:17:12.137825
5	Meet Our Team	This is the team behind the marvelous InLet Laboratory	692fd696440fd.jpeg	See us!	index.php#team	2	f	2025-11-30 19:58:37.567632	2025-12-08 13:41:40.787518
\.


--
-- TOC entry 3732 (class 0 OID 66381)
-- Dependencies: 249
-- Data for Name: equipment; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.equipment (id, name, description, quantity, available_quantity, display_order, is_active, created_at, updated_at) FROM stdin;
3	Kamera	Kamera DSLR untuk dokumentasi	2	2	3	t	2025-11-16 00:18:17.060283	2025-12-01 21:04:13.071792
2	Proyektor	Proyektor untuk presentasi	1	1	999	t	2025-11-16 00:18:17.060283	2025-12-09 21:52:42.017226
9	Spidol	Spidol boardmarker bisa dihapus	5	5	999	t	2025-12-09 21:13:58.511665	2025-12-09 21:13:58.511665
1	Laptop	Laptop untuk presentasi dan kerja	10	10	1	t	2025-11-16 00:18:17.060283	2025-12-01 21:04:13.071792
\.


--
-- TOC entry 3712 (class 0 OID 41724)
-- Dependencies: 229
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (id, image, caption, is_active, created_at, updated_at) FROM stdin;
5	69089a6a06743.jpg	Feeding System	t	2025-11-03 19:04:58.027889	2025-11-03 19:04:58.027889
6	69089a7f28fc0.jpg	Neura Measurement	t	2025-11-03 19:05:19.169368	2025-11-03 19:05:19.169368
7	69089a88e7403.jpg	Smart City	t	2025-11-03 19:05:28.949253	2025-11-03 19:05:28.949253
8	69128b9f72a8d.jpg	IOT	t	2025-11-11 08:04:31.471778	2025-11-11 08:04:31.471778
10	693c5b0054410.jpg		t	2025-12-13 01:12:16.347492	2025-12-13 01:12:16.347492
11	693c5c4f351ec.jpg	Gallery2	t	2025-12-13 01:17:51.219398	2025-12-13 01:17:51.219398
12	693c5c57b9772.jpg	Gallery3	t	2025-12-13 01:17:59.761405	2025-12-13 01:17:59.761405
13	693c5c6ca06d6.jpg	Gallery4	t	2025-12-13 01:18:20.660009	2025-12-13 01:18:20.660009
14	693c5c789e516.jpeg	Gallery5	t	2025-12-13 01:18:32.650197	2025-12-13 01:18:32.650197
16	693c5d1b61a6a.jpg	Gallery6	t	2025-12-13 01:21:15.401604	2025-12-13 01:21:15.401604
\.


--
-- TOC entry 3740 (class 0 OID 74975)
-- Dependencies: 263
-- Data for Name: guestbook; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.guestbook (id, name, institution, email, phone, message, ip_address, user_agent, created_at, updated_at) FROM stdin;
1	Achmad Nabil Afgareza	Politeknik Negeri Malang	nabilafgareza12@gmail.com	081332160514	Inlet josjis	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	2025-11-28 20:11:59.413825	2025-11-28 20:11:59.413825
2	Qulbi Khutsi Azzumi	SMK 1 KEPANJEN	qulib@mail.co	081332160514	Aku cinta inlet	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	2025-11-28 20:40:59.118166	2025-11-28 20:40:59.118166
\.


--
-- TOC entry 3720 (class 0 OID 58176)
-- Dependencies: 237
-- Data for Name: lecturer_education; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_education (id, team_member_id, institution, degree, field_of_study, start_date, end_date, description, created_at) FROM stdin;
4	4	Example Institution	Example Degree	Example FOS	2025-11-18	2025-11-19	Example Description	2025-11-19 09:33:53.472466
2	5	Hiroshima University	Doctor of Engineering	Information Engineering	2016-05-11	2020-05-11	Learning about information engineering in hiroshima university japan	2025-11-11 08:16:27.072312
\.


--
-- TOC entry 3724 (class 0 OID 58205)
-- Dependencies: 241
-- Data for Name: lecturer_interests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_interests (id, team_member_id, interest_text, created_at) FROM stdin;
2	4	Coding	2025-11-11 08:18:58.702908
3	5	Learning Engineering	2025-12-07 15:00:21.944811
\.


--
-- TOC entry 3718 (class 0 OID 58160)
-- Dependencies: 235
-- Data for Name: lecturer_projects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_projects (id, team_member_id, title, category, description, start_date, end_date, status, created_at) FROM stdin;
3	4	System Quiz Anti Cheat	Research	Sistem yang digunakan untuk mencegah mahasiswa untuk mencontek saat ujian	2025-11-11	\N	active	2025-11-12 08:52:05.372429
4	4	Gerbang Belajar	Development	Website gerbang belajar untuk ujian	2025-11-18	\N	active	2025-11-18 20:09:16.635163
2	5	Example Projects 2	Research	Example Description 1	2025-11-11	\N	active	2025-11-11 08:14:56.601143
\.


--
-- TOC entry 3726 (class 0 OID 58220)
-- Dependencies: 243
-- Data for Name: lecturer_publications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_publications (id, team_member_id, title, year, url, type, created_at) FROM stdin;
2	4	Publication Example	2025		journal	2025-11-19 09:23:03.246736
3	5	Improving English reading for EFL readers with reviewing kit-build concept map	2020	https://telrp.springeropen.com/articles/10.1186/s41039-020-00126-8	journal	2025-12-07 16:04:07.496225
\.


--
-- TOC entry 3722 (class 0 OID 58191)
-- Dependencies: 239
-- Data for Name: lecturer_skills; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_skills (id, team_member_id, category, skill_name, skill_icon, proficiency, created_at) FROM stdin;
1	4	Programming	PHP	fab fa-php	3	2025-11-11 00:32:09.613457
3	5	Design	UI/UX Designer		3	2025-12-07 15:54:41.508001
4	5	Programming	Ruby		3	2025-12-07 15:55:50.205989
2	5	Programming	CSS		3	2025-11-19 09:54:56.041073
5	5	Programming	HTML		3	2025-12-07 15:57:04.905743
6	5	Programming	Java		3	2025-12-07 15:57:34.482707
7	5	Programming	MySQL		3	2025-12-07 15:57:47.675754
\.


--
-- TOC entry 3710 (class 0 OID 41711)
-- Dependencies: 227
-- Data for Name: news; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.news (id, title, content, image, author, published_at, is_active, created_at, updated_at) FROM stdin;
3	ICCE 2024 - Atteneo University, Phillipines	3 of our member went to Phillipines to present our research. It's been a valuable experiences to meet other researcher's outside Indonesia	69356f9bf3dcc.jpg	Admin	2025-11-02 22:58:40.299109	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	ICAST 2024 - Bandung, Indonesia	Thrilled and deeply honored to receive the Best Paper Award at ICAST 2024! A special thank you to Professor Hee-Deung Park from Korea University and Dr. Ong Tze Ching from Kuching Polytechnic, Malaysia, for their recognition of my work. This achievement reflects the dedication and passion for advancing research in our field. Grateful for this moment and excited for the journey ahead.	69356fd4bd39d.jpg	Administrator	2025-11-06 17:28:27.430025	t	2025-11-06 17:28:27.430025	2025-11-06 17:28:27.430025
1	ECTEL 2024 - Krems, Austria	Introducing VIAT-map to other researcher in ECTEL conference	6935701947d17.jpg	Admin	2025-11-02 22:58:40.299109	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
5	Best Overall Paper Award	Enriching the research area by having Best overall paper award in ICCE 2023, Shimane Japan	69357094979b2.jpg	Admin	2025-11-26 22:44:41.679073	t	2025-11-26 22:44:41.679073	2025-11-26 22:44:41.679073
\.


--
-- TOC entry 3706 (class 0 OID 41685)
-- Dependencies: 223
-- Data for Name: partners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.partners (id, name, logo, website, display_order, is_active, created_at, updated_at) FROM stdin;
3	Research Institute	690784163d836.png	https://example.com	3	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
2	Standfort	690783dc76756.png	https://example.com	1	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
1	Harvard	690783b52eef8.png	https://example.com	2	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	Unesa	690790d53181b.png		2	t	2025-11-03 00:09:52.309106	2025-11-03 00:09:52.309106
7	Unhas	691139e02264f.png		4	t	2025-11-10 08:03:28.145582	2025-11-10 08:03:28.145582
9	Hiroshima University lead by Prof.Tsukasa Hirashima	692d936f0cdf6.png	https://www.hiroshima-u.ac.jp/en	6	t	2025-12-01 20:09:03.055435	2025-12-01 20:09:03.055435
\.


--
-- TOC entry 3704 (class 0 OID 41673)
-- Dependencies: 221
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products (id, name, description, image, full_description, features, is_active, created_at, updated_at, year, demo_link) FROM stdin;
1	Learning Management System	Comprehensive LMS solution for modern education	6908a5433fd62.png	A full-featured learning management system designed for educational institutions.	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	2025	\N
3	Viat Map Application	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept	6908a58634b35.png	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept. We are trying to emphasise the logic behind a written text by adding the claim, ground and warrant following the Toulmin Argument Concept.	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	2025	\N
5	Robot Human Like	The greates robot human like has ever built!	69272154a133d.png	this is full description of The greates robot human like has ever built!, The greates robot human like has ever built!, The greates robot human like has ever built!, The greates robot human like has ever built!	\N	t	2025-11-26 22:48:36.662026	2025-11-26 22:48:36.662026	2025	\N
2	PseudoLearn Application	Sebuah media pembelajaran rekonstruksi algoritma pseudocode	6908a6cb2e997.png	Sebuah media pembelajaran rekonstruksi algoritma pseudocode dengan menggunakan pendekatan Element Fill-in-Blank Problems di dalam pemrograman java	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	2024	https://pseudolearn.id/
\.


--
-- TOC entry 3714 (class 0 OID 41736)
-- Dependencies: 231
-- Data for Name: research_activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_activities (id, title, description, image, full_description, is_active, created_at, updated_at, youtube_url) FROM stdin;
1	AI in Education	Exploring the use of artificial intelligence in personalized learning	690af0340c1bc.jpg	Exploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learning	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	\N
3	Data Analytics	Using big data to improve learning outcomes	692d418dcd129.png	Using big data to improve learning outcomes is always perfect for any needs that require advance research	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	\N
2	What is TEL?	semi-automatic concept mapping tool—a compromise between fully manual and fully automatic generation methods. 	690aed549f6de.jpg	This tool aims to support teachers in authoring concept maps more efficiently by recommending components extracted via Natural Language Processing (NLP) techniques from educational texts, while still allowing teacher control to ensure the accuracy and relevance of the maps.\r\n\r\nAdditionally, the speaker elaborates on a related interactive learning tool called “Monosaget”, which supports problem posing activities, primarily in mathematics education, helping students construct and understand problem structures through drag-and-drop sentence composition. The research also includes extensive data analytics on student interactions to evaluate learning patterns, cognitive understanding, and difficulties.	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	https://www.youtube.com/watch?v=eis5aTweBHs
\.


--
-- TOC entry 3742 (class 0 OID 75020)
-- Dependencies: 266
-- Data for Name: site_settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.site_settings (id, logo_path, logo_alt, footer_about_title, footer_about_text, contact_email, contact_phone, contact_address, social_facebook, social_instagram, social_youtube, social_linkedin, social_twitter, quick_links, updated_at, updated_by, footer_copyright, borrowing_notification_email) FROM stdin;
1	assets/images/site/logo_1764727051.png	INLET Logo	InLet	Information and Learning Engineering Technology Laboratory	inlet@polinema.ac.id	+62 341 404424	Jl. Soekarno Hatta No.9, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141.	https://facebook.com/inlet.polinema	https://instagram.com/inlet.polinema	https://youtube.com/@inlet.polinema	\N	\N	[{"label":"Home","url":"index.php"},{"label":"About","url":"index.php?page=about"},{"label":"Peminjaman","url":"index.php?page=borrowing"},{"label":"Absensi","url":"index.php?page=attendance"}]	2025-12-03 08:57:31.230424	1	© 2025 InLet. All rights reserved.	inletpolinema@gmail.com
\.


--
-- TOC entry 3728 (class 0 OID 66352)
-- Dependencies: 245
-- Data for Name: student_attendance; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.student_attendance (id, nama, nim, attendance_type, attendance_date, attendance_time, created_at, ip_address, user_agent, student_id) FROM stdin;
25	\N	\N	datang	2025-12-09	20:52:43.469686	2025-12-09 20:52:43.469686	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	9
\.


--
-- TOC entry 3736 (class 0 OID 66617)
-- Dependencies: 254
-- Data for Name: students; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.students (id, nim, name, email, phone, program_study, semester, is_active, created_at, updated_at) FROM stdin;
9	244107020006	Raditya Riefky	radit@mail.co	088888888	S-2 Rekayasa Teknologi Informasi	2	t	2025-12-09 20:43:09.308434	2025-12-09 20:52:19.408583
\.


--
-- TOC entry 3708 (class 0 OID 41698)
-- Dependencies: 225
-- Data for Name: team_members; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.team_members (id, name, "position", photo, bio, email, linkedin, display_order, is_active, created_at, updated_at, address, phone, linkedin_url, google_scholar_url, researchgate_url, orcid_url) FROM stdin;
7	John Doe	Junior Researcher	693545c2650ab.jpg	Hi!, im john doe	john@mail.co	\N	4	t	2025-12-07 16:15:46.417174	2025-12-07 16:15:46.417174	\N	\N	https://www.linkedin.com/in/nabilfgrza	\N	\N	\N
2	Jane Smith	Senior Researcher	69128ab0ca09b.jpeg		jane@inlet.com	https://www.linkedin.com/in/nabilfgrza/	2	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	\N	\N	\N	\N	\N	\N
5	Dr. Eng. Banni Satria Andoko, S.Kom.,M.MSI	Lead Laboratory	6907822140adb.jpeg	Dosen Magister	pakbanni@mail.co	https://www.linkedin.com/in/nabilfgrza/	1	t	2025-11-02 23:09:05.266806	2025-11-02 23:09:05.266806	Polinema	0813321321	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza
4	Putra Prima Arhandi, S.T.,M.Kom.	Lead Software Engineer	69265df159175.jpeg		mrpytra@example.mail	http://www.linkedin.com/in/nabilfgrza	1	t	2025-11-02 23:08:24.600623	2025-11-02 23:08:24.600623	Jl.Alamat Dummy	81332160514	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza
\.


--
-- TOC entry 3716 (class 0 OID 58149)
-- Dependencies: 233
-- Data for Name: visitors; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.visitors (id, ip_address, user_agent, country, country_code, page_url, visited_at) FROM stdin;
1	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-06 17:42:16.940872
2	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-06 19:08:50.755204
3	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-07 08:41:04.106382
4	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-10 07:57:54.799819
5	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-10 13:18:36.14306
6	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-10 14:28:36.751902
7	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-10 16:13:25.38145
8	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-10 19:34:04.251787
16	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=lecturer-detail&id=4	2025-11-12 08:52:09.211513
46	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-27 11:37:54.706476
10	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0	Local	LOCAL	/inlet-web/index.php	2025-11-10 20:11:47.742361
33	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-19 10:53:58.740858
17	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-12 19:34:47.908024
18	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-12 20:17:02.676808
11	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=lecturer-detail&id=4	2025-11-11 01:18:23.100258
40	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-25 22:56:24.989931
13	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 08:38:28.502299
22	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-16 01:32:05.199532
19	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-12 22:20:36.872909
20	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-14 10:59:54.06802
23	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-17 08:45:20.061283
34	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-19 19:35:28.423104
35	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=about	2025-11-19 20:19:34.828734
12	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 06:48:14.455743
38	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=news-detail&id=1	2025-11-23 20:25:50.838483
24	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-17 11:41:06.333824
9	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-10 21:21:03.030865
31	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-18 20:38:32.289652
14	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 20:13:16.506225
36	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=about	2025-11-20 08:22:46.777626
37	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=about	2025-11-20 14:50:45.080872
15	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 22:08:09.40116
26	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-17 15:34:25.896093
27	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-18 00:22:36.942692
28	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-18 00:59:20.255751
21	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-15 22:55:45.600907
32	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-19 08:36:03.274899
29	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-18 01:41:08.932591
30	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-18 10:40:36.696206
25	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-17 12:59:07.690854
48	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-28 20:55:47.992549
49	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-29 18:54:43.150079
69	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-09 19:21:58.907594
73	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-14 00:52:37.239169
39	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-25 20:55:16.844052
41	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0	Local	LOCAL	/inlet-web/index.php	2025-11-25 22:55:28.992859
42	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-26 08:02:38.392943
51	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-01 06:30:18.988709
56	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:145.0) Gecko/20100101 Firefox/145.0	Local	LOCAL	/inlet-web/index.php	2025-12-01 13:43:29.422094
44	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-26 23:02:15.126186
70	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-12-09 21:14:02.536001
43	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=about	2025-11-26 09:14:37.530468
55	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-01 15:36:02.05368
45	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=attendance	2025-11-27 09:13:16.465528
47	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=attendance	2025-11-28 08:49:56.893723
66	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-07 20:05:02.341441
52	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-01 08:24:37.301872
53	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-12-01 09:43:03.103755
54	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-01 12:16:53.999689
62	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-04 16:58:28.076386
63	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-04 19:09:52.01806
50	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-30 21:36:45.695862
57	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-12-01 23:10:33.959327
58	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-12-02 00:02:02.382065
59	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-12-02 00:49:15.658707
72	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-13 02:00:15.144055
71	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-09 22:10:31.557066
67	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-08 13:50:17.898451
65	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-07 16:22:42.355497
64	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-05 20:05:46.554339
68	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-09 10:38:49.376936
60	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-03 09:33:43.158564
61	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-12-03 13:22:31.649578
\.


--
-- TOC entry 3806 (class 0 OID 0)
-- Dependencies: 259
-- Name: about_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.about_settings_id_seq', 2, true);


--
-- TOC entry 3807 (class 0 OID 0)
-- Dependencies: 214
-- Name: admin_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_users_id_seq', 1, true);


--
-- TOC entry 3808 (class 0 OID 0)
-- Dependencies: 250
-- Name: borrowing_equipment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.borrowing_equipment_id_seq', 26, true);


--
-- TOC entry 3809 (class 0 OID 0)
-- Dependencies: 247
-- Name: borrowings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.borrowings_id_seq', 36, true);


--
-- TOC entry 3810 (class 0 OID 0)
-- Dependencies: 218
-- Name: card_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.card_data_id_seq', 4, true);


--
-- TOC entry 3811 (class 0 OID 0)
-- Dependencies: 216
-- Name: carousel_banners_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.carousel_banners_id_seq', 5, true);


--
-- TOC entry 3812 (class 0 OID 0)
-- Dependencies: 248
-- Name: equipment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.equipment_id_seq', 10, true);


--
-- TOC entry 3813 (class 0 OID 0)
-- Dependencies: 228
-- Name: gallery_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_id_seq', 16, true);


--
-- TOC entry 3814 (class 0 OID 0)
-- Dependencies: 262
-- Name: guestbook_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.guestbook_id_seq', 2, true);


--
-- TOC entry 3815 (class 0 OID 0)
-- Dependencies: 236
-- Name: lecturer_education_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_education_id_seq', 4, true);


--
-- TOC entry 3816 (class 0 OID 0)
-- Dependencies: 240
-- Name: lecturer_interests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_interests_id_seq', 3, true);


--
-- TOC entry 3817 (class 0 OID 0)
-- Dependencies: 234
-- Name: lecturer_projects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_projects_id_seq', 4, true);


--
-- TOC entry 3818 (class 0 OID 0)
-- Dependencies: 242
-- Name: lecturer_publications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_publications_id_seq', 3, true);


--
-- TOC entry 3819 (class 0 OID 0)
-- Dependencies: 238
-- Name: lecturer_skills_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_skills_id_seq', 7, true);


--
-- TOC entry 3820 (class 0 OID 0)
-- Dependencies: 226
-- Name: news_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.news_id_seq', 5, true);


--
-- TOC entry 3821 (class 0 OID 0)
-- Dependencies: 222
-- Name: partners_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.partners_id_seq', 10, true);


--
-- TOC entry 3822 (class 0 OID 0)
-- Dependencies: 220
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_id_seq', 5, true);


--
-- TOC entry 3823 (class 0 OID 0)
-- Dependencies: 230
-- Name: research_activities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_activities_id_seq', 3, true);


--
-- TOC entry 3824 (class 0 OID 0)
-- Dependencies: 265
-- Name: site_settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.site_settings_id_seq', 1, false);


--
-- TOC entry 3825 (class 0 OID 0)
-- Dependencies: 244
-- Name: student_attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.student_attendance_id_seq', 25, true);


--
-- TOC entry 3826 (class 0 OID 0)
-- Dependencies: 253
-- Name: students_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.students_id_seq', 9, true);


--
-- TOC entry 3827 (class 0 OID 0)
-- Dependencies: 224
-- Name: team_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.team_members_id_seq', 7, true);


--
-- TOC entry 3828 (class 0 OID 0)
-- Dependencies: 232
-- Name: visitors_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitors_id_seq', 73, true);


--
-- TOC entry 3524 (class 2606 OID 66746)
-- Name: about_settings about_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.about_settings
    ADD CONSTRAINT about_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 3449 (class 2606 OID 41647)
-- Name: admin_users admin_users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_email_key UNIQUE (email);


--
-- TOC entry 3451 (class 2606 OID 41643)
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- TOC entry 3453 (class 2606 OID 41645)
-- Name: admin_users admin_users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_username_key UNIQUE (username);


--
-- TOC entry 3511 (class 2606 OID 66546)
-- Name: borrowing_equipment borrowing_equipment_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowing_equipment
    ADD CONSTRAINT borrowing_equipment_pkey PRIMARY KEY (id);


--
-- TOC entry 3497 (class 2606 OID 66379)
-- Name: borrowings borrowings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings
    ADD CONSTRAINT borrowings_pkey PRIMARY KEY (id);


--
-- TOC entry 3457 (class 2606 OID 41671)
-- Name: card_data card_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.card_data
    ADD CONSTRAINT card_data_pkey PRIMARY KEY (id);


--
-- TOC entry 3455 (class 2606 OID 41660)
-- Name: carousel_banners carousel_banners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners
    ADD CONSTRAINT carousel_banners_pkey PRIMARY KEY (id);


--
-- TOC entry 3505 (class 2606 OID 66396)
-- Name: equipment equipment_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment
    ADD CONSTRAINT equipment_name_key UNIQUE (name);


--
-- TOC entry 3507 (class 2606 OID 66394)
-- Name: equipment equipment_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment
    ADD CONSTRAINT equipment_pkey PRIMARY KEY (id);


--
-- TOC entry 3467 (class 2606 OID 41734)
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (id);


--
-- TOC entry 3526 (class 2606 OID 74984)
-- Name: guestbook guestbook_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.guestbook
    ADD CONSTRAINT guestbook_pkey PRIMARY KEY (id);


--
-- TOC entry 3478 (class 2606 OID 58184)
-- Name: lecturer_education lecturer_education_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education
    ADD CONSTRAINT lecturer_education_pkey PRIMARY KEY (id);


--
-- TOC entry 3484 (class 2606 OID 58213)
-- Name: lecturer_interests lecturer_interests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests
    ADD CONSTRAINT lecturer_interests_pkey PRIMARY KEY (id);


--
-- TOC entry 3475 (class 2606 OID 58169)
-- Name: lecturer_projects lecturer_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects
    ADD CONSTRAINT lecturer_projects_pkey PRIMARY KEY (id);


--
-- TOC entry 3487 (class 2606 OID 58229)
-- Name: lecturer_publications lecturer_publications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications
    ADD CONSTRAINT lecturer_publications_pkey PRIMARY KEY (id);


--
-- TOC entry 3481 (class 2606 OID 58198)
-- Name: lecturer_skills lecturer_skills_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills
    ADD CONSTRAINT lecturer_skills_pkey PRIMARY KEY (id);


--
-- TOC entry 3465 (class 2606 OID 41722)
-- Name: news news_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.news
    ADD CONSTRAINT news_pkey PRIMARY KEY (id);


--
-- TOC entry 3461 (class 2606 OID 41696)
-- Name: partners partners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners
    ADD CONSTRAINT partners_pkey PRIMARY KEY (id);


--
-- TOC entry 3459 (class 2606 OID 41683)
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- TOC entry 3469 (class 2606 OID 41746)
-- Name: research_activities research_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_activities
    ADD CONSTRAINT research_activities_pkey PRIMARY KEY (id);


--
-- TOC entry 3532 (class 2606 OID 75042)
-- Name: site_settings site_settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.site_settings
    ADD CONSTRAINT site_settings_pkey PRIMARY KEY (id);


--
-- TOC entry 3493 (class 2606 OID 66363)
-- Name: student_attendance student_attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_pkey PRIMARY KEY (id);


--
-- TOC entry 3520 (class 2606 OID 66629)
-- Name: students students_nim_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_nim_key UNIQUE (nim);


--
-- TOC entry 3522 (class 2606 OID 66627)
-- Name: students students_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.students
    ADD CONSTRAINT students_pkey PRIMARY KEY (id);


--
-- TOC entry 3463 (class 2606 OID 41709)
-- Name: team_members team_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_pkey PRIMARY KEY (id);


--
-- TOC entry 3515 (class 2606 OID 66548)
-- Name: borrowing_equipment uk_borrowing_equipment; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowing_equipment
    ADD CONSTRAINT uk_borrowing_equipment UNIQUE (borrowing_id, equipment_id);


--
-- TOC entry 3495 (class 2606 OID 66644)
-- Name: student_attendance unique_attendance_student; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT unique_attendance_student UNIQUE (student_id, attendance_date, attendance_type);


--
-- TOC entry 3472 (class 2606 OID 58157)
-- Name: visitors visitors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitors
    ADD CONSTRAINT visitors_pkey PRIMARY KEY (id);


--
-- TOC entry 3488 (class 1259 OID 66366)
-- Name: idx_attendance_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_date ON public.student_attendance USING btree (attendance_date DESC);


--
-- TOC entry 3489 (class 1259 OID 66367)
-- Name: idx_attendance_nim; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_nim ON public.student_attendance USING btree (nim);


--
-- TOC entry 3490 (class 1259 OID 66645)
-- Name: idx_attendance_student_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_student_id ON public.student_attendance USING btree (student_id);


--
-- TOC entry 3491 (class 1259 OID 66368)
-- Name: idx_attendance_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_type ON public.student_attendance USING btree (attendance_type);


--
-- TOC entry 3512 (class 1259 OID 66559)
-- Name: idx_borrowing_equipment_borrowing_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowing_equipment_borrowing_id ON public.borrowing_equipment USING btree (borrowing_id);


--
-- TOC entry 3513 (class 1259 OID 66560)
-- Name: idx_borrowing_equipment_equipment_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowing_equipment_equipment_id ON public.borrowing_equipment USING btree (equipment_id);


--
-- TOC entry 3498 (class 1259 OID 66599)
-- Name: idx_borrowings_borrower; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_borrower ON public.borrowings USING btree (borrower_name);


--
-- TOC entry 3499 (class 1259 OID 66420)
-- Name: idx_borrowings_dates; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_dates ON public.borrowings USING btree (borrow_date, return_date);


--
-- TOC entry 3500 (class 1259 OID 66421)
-- Name: idx_borrowings_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_status ON public.borrowings USING btree (status);


--
-- TOC entry 3501 (class 1259 OID 66598)
-- Name: idx_borrowings_status_dates; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_status_dates ON public.borrowings USING btree (status, borrow_date, return_date);


--
-- TOC entry 3502 (class 1259 OID 66422)
-- Name: idx_borrowings_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_type ON public.borrowings USING btree (type);


--
-- TOC entry 3503 (class 1259 OID 66597)
-- Name: idx_borrowings_type_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_type_status ON public.borrowings USING btree (type, status);


--
-- TOC entry 3508 (class 1259 OID 66423)
-- Name: idx_equipment_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_equipment_active ON public.equipment USING btree (is_active);


--
-- TOC entry 3509 (class 1259 OID 66600)
-- Name: idx_equipment_availability; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_equipment_availability ON public.equipment USING btree (is_active, available_quantity);


--
-- TOC entry 3527 (class 1259 OID 74985)
-- Name: idx_guestbook_created_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_guestbook_created_at ON public.guestbook USING btree (created_at DESC);


--
-- TOC entry 3528 (class 1259 OID 74986)
-- Name: idx_guestbook_email; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_guestbook_email ON public.guestbook USING btree (email);


--
-- TOC entry 3529 (class 1259 OID 74987)
-- Name: idx_guestbook_institution; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_guestbook_institution ON public.guestbook USING btree (institution);


--
-- TOC entry 3470 (class 1259 OID 58158)
-- Name: idx_ip_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ip_date ON public.visitors USING btree (ip_address, visited_at);


--
-- TOC entry 3476 (class 1259 OID 58236)
-- Name: idx_lecturer_education_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_education_member ON public.lecturer_education USING btree (team_member_id);


--
-- TOC entry 3482 (class 1259 OID 58238)
-- Name: idx_lecturer_interests_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_interests_member ON public.lecturer_interests USING btree (team_member_id);


--
-- TOC entry 3473 (class 1259 OID 58235)
-- Name: idx_lecturer_projects_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_projects_member ON public.lecturer_projects USING btree (team_member_id);


--
-- TOC entry 3485 (class 1259 OID 58239)
-- Name: idx_lecturer_publications_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_publications_member ON public.lecturer_publications USING btree (team_member_id);


--
-- TOC entry 3479 (class 1259 OID 58237)
-- Name: idx_lecturer_skills_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_skills_member ON public.lecturer_skills USING btree (team_member_id);


--
-- TOC entry 3530 (class 1259 OID 75044)
-- Name: idx_site_settings_updated_at; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_site_settings_updated_at ON public.site_settings USING btree (updated_at);


--
-- TOC entry 3516 (class 1259 OID 66632)
-- Name: idx_students_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_students_active ON public.students USING btree (is_active);


--
-- TOC entry 3517 (class 1259 OID 66631)
-- Name: idx_students_name; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_students_name ON public.students USING btree (name);


--
-- TOC entry 3518 (class 1259 OID 66630)
-- Name: idx_students_nim; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_students_nim ON public.students USING btree (nim);


--
-- TOC entry 3543 (class 2620 OID 66594)
-- Name: borrowing_equipment trigger_equipment_quantity_on_borrowing; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_equipment_quantity_on_borrowing AFTER INSERT OR DELETE ON public.borrowing_equipment FOR EACH ROW EXECUTE FUNCTION public.update_equipment_quantity_on_borrowing();


--
-- TOC entry 3541 (class 2620 OID 66596)
-- Name: borrowings trigger_equipment_quantity_on_return; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_equipment_quantity_on_return AFTER UPDATE ON public.borrowings FOR EACH ROW WHEN (((old.status)::text IS DISTINCT FROM (new.status)::text)) EXECUTE FUNCTION public.update_equipment_quantity_on_return();


--
-- TOC entry 3544 (class 2620 OID 66667)
-- Name: students trigger_students_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_students_updated_at BEFORE UPDATE ON public.students FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- TOC entry 3542 (class 2620 OID 66592)
-- Name: borrowings trigger_update_borrowing_timestamp; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_update_borrowing_timestamp BEFORE UPDATE ON public.borrowings FOR EACH ROW EXECUTE FUNCTION public.update_borrowing_timestamp();


--
-- TOC entry 3546 (class 2620 OID 75043)
-- Name: site_settings trigger_update_site_settings_timestamp; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_update_site_settings_timestamp BEFORE UPDATE ON public.site_settings FOR EACH ROW EXECUTE FUNCTION public.update_site_settings_timestamp();


--
-- TOC entry 3545 (class 2620 OID 74989)
-- Name: guestbook update_guestbook_timestamp_trigger; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_guestbook_timestamp_trigger BEFORE UPDATE ON public.guestbook FOR EACH ROW EXECUTE FUNCTION public.update_guestbook_timestamp();


--
-- TOC entry 3538 (class 2606 OID 66638)
-- Name: student_attendance fk_attendance_student; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES public.students(id) ON DELETE CASCADE;


--
-- TOC entry 3539 (class 2606 OID 66549)
-- Name: borrowing_equipment fk_borrowing_equipment_borrowing; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowing_equipment
    ADD CONSTRAINT fk_borrowing_equipment_borrowing FOREIGN KEY (borrowing_id) REFERENCES public.borrowings(id) ON DELETE CASCADE;


--
-- TOC entry 3540 (class 2606 OID 66554)
-- Name: borrowing_equipment fk_borrowing_equipment_equipment; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowing_equipment
    ADD CONSTRAINT fk_borrowing_equipment_equipment FOREIGN KEY (equipment_id) REFERENCES public.equipment(id) ON DELETE RESTRICT;


--
-- TOC entry 3534 (class 2606 OID 58185)
-- Name: lecturer_education lecturer_education_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education
    ADD CONSTRAINT lecturer_education_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- TOC entry 3536 (class 2606 OID 58214)
-- Name: lecturer_interests lecturer_interests_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests
    ADD CONSTRAINT lecturer_interests_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- TOC entry 3533 (class 2606 OID 58170)
-- Name: lecturer_projects lecturer_projects_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects
    ADD CONSTRAINT lecturer_projects_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- TOC entry 3537 (class 2606 OID 58230)
-- Name: lecturer_publications lecturer_publications_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications
    ADD CONSTRAINT lecturer_publications_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- TOC entry 3535 (class 2606 OID 58199)
-- Name: lecturer_skills lecturer_skills_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills
    ADD CONSTRAINT lecturer_skills_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- TOC entry 3763 (class 0 OID 0)
-- Dependencies: 251
-- Name: TABLE borrowing_equipment; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.borrowing_equipment TO PUBLIC;


--
-- TOC entry 3768 (class 0 OID 0)
-- Dependencies: 250
-- Name: SEQUENCE borrowing_equipment_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT USAGE ON SEQUENCE public.borrowing_equipment_id_seq TO PUBLIC;


--
-- TOC entry 3773 (class 0 OID 0)
-- Dependencies: 252
-- Name: TABLE equipment_availability_view; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT ON TABLE public.equipment_availability_view TO PUBLIC;


-- Completed on 2025-12-19 09:16:54

--
-- PostgreSQL database dump complete
--

\unrestrict 0U5u70cZDlwS6rPlYjCtoPp9cMrxRcr3kecBfcWYQABHKfPpgA8fDKJxKdnmOgz

