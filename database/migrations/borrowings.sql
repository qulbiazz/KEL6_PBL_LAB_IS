--
-- PostgreSQL database dump
--

\restrict S9DUx6VRZJbcIzibAHf9dPyXeoitfthmIEYNznXrIegRCgQpZzYneDMhgwWsnRR

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

-- Started on 2025-11-15 15:09:54

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 247 (class 1259 OID 19030)
-- Name: borrowings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.borrowings (
    id integer NOT NULL,
    borrower_name character varying(100) NOT NULL,
    occupation character varying(50) NOT NULL,
    type character varying(20) NOT NULL,
    item_name text NOT NULL,
    borrow_date date NOT NULL,
    return_date date NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT borrowings_type_check CHECK (((type)::text = ANY ((ARRAY['equipment'::character varying, 'room'::character varying])::text[])))
);


ALTER TABLE public.borrowings OWNER TO postgres;

--
-- TOC entry 246 (class 1259 OID 19029)
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
-- TOC entry 3390 (class 0 OID 0)
-- Dependencies: 246
-- Name: borrowings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.borrowings_id_seq OWNED BY public.borrowings.id;


--
-- TOC entry 3236 (class 2604 OID 19033)
-- Name: borrowings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings ALTER COLUMN id SET DEFAULT nextval('public.borrowings_id_seq'::regclass);


--
-- TOC entry 3384 (class 0 OID 19030)
-- Dependencies: 247
-- Data for Name: borrowings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.borrowings (id, borrower_name, occupation, type, item_name, borrow_date, return_date, created_at) FROM stdin;
2	Qulbi Khutsi Azzumi	dosen	room	Laptop, Kamera	2025-11-15	2025-11-22	2025-11-15 12:48:49.106639
3	Qulbi Khutsi Azzumi	dosen	room	Laptop, Kamera	2025-11-15	2025-11-22	2025-11-15 12:52:42.340179
4	Jumiatin	Mahasiswa Magang	room	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 12:53:14.767298
5	Jumiatin	Mahasiswa Magang	room	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 12:58:53.840989
6	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 12:59:54.023605
7	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:04:27.7934
8	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:04:30.467878
9	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:13:42.402126
10	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:13:44.675444
11	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:16:13.486079
12	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:16:16.010386
13	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:18:41.343303
14	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:24:40.455895
15	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:32:52.825074
16	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:33:02.788922
17	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:37:37.784563
18	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:37:39.672765
19	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:37:40.572943
20	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:37:41.423713
21	Qulbi Khutsi Azzumi	mahasiswa	room	Ruang Komputer	2025-11-15	2025-11-16	2025-11-15 13:37:41.667332
22	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:38:42.627045
23	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:47:15.90072
24	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:49:32.949936
25	budi	dosen	equipment	Laptop, Kamera	2025-11-15	2025-11-16	2025-11-15 13:49:54.88066
26	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:50:17.655653
27	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:50:33.196276
28	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:54:30.215195
29	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 13:56:46.628403
30	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:02:24.664596
31	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:02:29.038696
32	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:04:14.866323
33	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:05:37.277296
34	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:05:51.805454
35	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:06:20.131369
36	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:09:28.164373
37	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:09:30.83549
38	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:09:42.919715
39	Jumiatin	Mahasiswa Magang	equipment	Proyektor, Tripod	2025-11-15	2025-11-22	2025-11-15 14:11:15.544391
\.


--
-- TOC entry 3391 (class 0 OID 0)
-- Dependencies: 246
-- Name: borrowings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.borrowings_id_seq', 39, true);


--
-- TOC entry 3240 (class 2606 OID 19037)
-- Name: borrowings borrowings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings
    ADD CONSTRAINT borrowings_pkey PRIMARY KEY (id);


-- Completed on 2025-11-15 15:09:54

--
-- PostgreSQL database dump complete
--

\unrestrict S9DUx6VRZJbcIzibAHf9dPyXeoitfthmIEYNznXrIegRCgQpZzYneDMhgwWsnRR

