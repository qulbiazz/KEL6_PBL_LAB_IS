--
-- PostgreSQL database dump
--

\restrict XVgYYenCdhBEeho1Mo1oaOuhXFv2E2i1Sfc1e8wFC9JuVygJzKEs5XtzhIYEBDE

-- Dumped from database version 15.14
-- Dumped by pg_dump version 15.14

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
-- Name: admin_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.admin_users_id_seq OWNED BY public.admin_users.id;


--
-- Name: borrowings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.borrowings (
    id integer NOT NULL,
    borrower_name character varying(100) NOT NULL,
    occupation character varying(50) NOT NULL,
    type character varying(20) NOT NULL,
    item_name text NOT NULL,
    borrow_date timestamp without time zone NOT NULL,
    return_date timestamp without time zone NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    status character varying(20) DEFAULT 'active'::character varying,
    item_id integer,
    item_type character varying(20),
    CONSTRAINT borrowings_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'returned'::character varying, 'cancelled'::character varying])::text[]))),
    CONSTRAINT borrowings_type_check CHECK (((type)::text = ANY (ARRAY[('equipment'::character varying)::text, ('room'::character varying)::text])))
);


ALTER TABLE public.borrowings OWNER TO postgres;

--
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
-- Name: borrowings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.borrowings_id_seq OWNED BY public.borrowings.id;


--
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
-- Name: card_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.card_data_id_seq OWNED BY public.card_data.id;


--
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
-- Name: carousel_banners_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.carousel_banners_id_seq OWNED BY public.carousel_banners.id;


--
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
-- Name: equipment_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.equipment_id_seq OWNED BY public.equipment.id;


--
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
-- Name: gallery_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.gallery_id_seq OWNED BY public.gallery.id;


--
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
-- Name: lecturer_education_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_education_id_seq OWNED BY public.lecturer_education.id;


--
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
-- Name: lecturer_interests_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_interests_id_seq OWNED BY public.lecturer_interests.id;


--
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
-- Name: lecturer_projects_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_projects_id_seq OWNED BY public.lecturer_projects.id;


--
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
-- Name: lecturer_publications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_publications_id_seq OWNED BY public.lecturer_publications.id;


--
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
-- Name: lecturer_skills_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.lecturer_skills_id_seq OWNED BY public.lecturer_skills.id;


--
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
-- Name: news_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.news_id_seq OWNED BY public.news.id;


--
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
-- Name: partners_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.partners_id_seq OWNED BY public.partners.id;


--
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
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.products OWNER TO postgres;

--
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
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
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
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.research_activities OWNER TO postgres;

--
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
-- Name: research_activities_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.research_activities_id_seq OWNED BY public.research_activities.id;


--
-- Name: rooms; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.rooms (
    id integer NOT NULL,
    name character varying(100) NOT NULL,
    description text,
    capacity integer,
    display_order integer DEFAULT 999,
    is_active boolean DEFAULT true,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.rooms OWNER TO postgres;

--
-- Name: rooms_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.rooms_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rooms_id_seq OWNER TO postgres;

--
-- Name: rooms_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.rooms_id_seq OWNED BY public.rooms.id;


--
-- Name: student_attendance; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.student_attendance (
    id integer NOT NULL,
    nama character varying(255) NOT NULL,
    nim character varying(50) NOT NULL,
    attendance_type character varying(10) NOT NULL,
    attendance_date date DEFAULT CURRENT_DATE NOT NULL,
    attendance_time time without time zone DEFAULT CURRENT_TIME NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    ip_address character varying(45),
    user_agent text,
    CONSTRAINT student_attendance_attendance_type_check CHECK (((attendance_type)::text = ANY ((ARRAY['datang'::character varying, 'pulang'::character varying])::text[])))
);


ALTER TABLE public.student_attendance OWNER TO postgres;

--
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
-- Name: student_attendance_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.student_attendance_id_seq OWNED BY public.student_attendance.id;


--
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
-- Name: team_members_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.team_members_id_seq OWNED BY public.team_members.id;


--
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
-- Name: visitors_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.visitors_id_seq OWNED BY public.visitors.id;


--
-- Name: admin_users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users ALTER COLUMN id SET DEFAULT nextval('public.admin_users_id_seq'::regclass);


--
-- Name: borrowings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings ALTER COLUMN id SET DEFAULT nextval('public.borrowings_id_seq'::regclass);


--
-- Name: card_data id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.card_data ALTER COLUMN id SET DEFAULT nextval('public.card_data_id_seq'::regclass);


--
-- Name: carousel_banners id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners ALTER COLUMN id SET DEFAULT nextval('public.carousel_banners_id_seq'::regclass);


--
-- Name: equipment id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment ALTER COLUMN id SET DEFAULT nextval('public.equipment_id_seq'::regclass);


--
-- Name: gallery id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery ALTER COLUMN id SET DEFAULT nextval('public.gallery_id_seq'::regclass);


--
-- Name: lecturer_education id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education ALTER COLUMN id SET DEFAULT nextval('public.lecturer_education_id_seq'::regclass);


--
-- Name: lecturer_interests id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests ALTER COLUMN id SET DEFAULT nextval('public.lecturer_interests_id_seq'::regclass);


--
-- Name: lecturer_projects id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects ALTER COLUMN id SET DEFAULT nextval('public.lecturer_projects_id_seq'::regclass);


--
-- Name: lecturer_publications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications ALTER COLUMN id SET DEFAULT nextval('public.lecturer_publications_id_seq'::regclass);


--
-- Name: lecturer_skills id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills ALTER COLUMN id SET DEFAULT nextval('public.lecturer_skills_id_seq'::regclass);


--
-- Name: news id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.news ALTER COLUMN id SET DEFAULT nextval('public.news_id_seq'::regclass);


--
-- Name: partners id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners ALTER COLUMN id SET DEFAULT nextval('public.partners_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: research_activities id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_activities ALTER COLUMN id SET DEFAULT nextval('public.research_activities_id_seq'::regclass);


--
-- Name: rooms id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rooms ALTER COLUMN id SET DEFAULT nextval('public.rooms_id_seq'::regclass);


--
-- Name: student_attendance id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance ALTER COLUMN id SET DEFAULT nextval('public.student_attendance_id_seq'::regclass);


--
-- Name: team_members id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members ALTER COLUMN id SET DEFAULT nextval('public.team_members_id_seq'::regclass);


--
-- Name: visitors id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitors ALTER COLUMN id SET DEFAULT nextval('public.visitors_id_seq'::regclass);


--
-- Data for Name: admin_users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.admin_users (id, username, password, email, full_name, created_at, last_login) FROM stdin;
1	admin	$2y$10$2C6uvj28j8awouiuB6rn3.dRDbor0hXAX.bcTCct1TjHgYT1lE2Pu	admin@inlet.com	Administrator	2025-11-02 22:58:40.299109	2025-11-15 23:16:24.332382
\.


--
-- Data for Name: borrowings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.borrowings (id, borrower_name, occupation, type, item_name, borrow_date, return_date, created_at, status, item_id, item_type) FROM stdin;
1	Achmad Nabil Afgareza	Mahasiswa	equipment	Laptop	2025-11-16 12:00:00	2025-11-17 12:00:00	2025-11-16 01:26:50.548971	returned	1	equipment
2	Achmad Nabil Afgareza	Dosen	room	Ruang Komputer	2025-11-17 12:00:00	2025-11-18 12:00:00	2025-11-17 08:46:02.376908	active	1	room
\.


--
-- Data for Name: card_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.card_data (id, icon, value, label, display_order, is_active, created_at, updated_at) FROM stdin;
1	fas fa-project-diagram	50+	Projects	1	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
2	fas fa-users	30+	Team Members	2	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
3	fas fa-file-alt	100+	Publications	3	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	fas fa-handshake	20+	Partners	4	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
\.


--
-- Data for Name: carousel_banners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.carousel_banners (id, title, description, image, button_text, button_link, display_order, is_active, created_at, updated_at) FROM stdin;
1	Learning Engineering Technology  Research Group	Find amazing Learning application tailored for you. Handy connects you with amazing Learning Engineering professionals.	690c78107a982.jpg	Learn More	index.php?page=about	1	t	2025-11-02 22:58:40.299109	2025-11-06 17:27:28.508366
\.


--
-- Data for Name: equipment; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.equipment (id, name, description, quantity, available_quantity, display_order, is_active, created_at, updated_at) FROM stdin;
5	Microphone	Microphone wireless untuk acara	3	3	5	t	2025-11-16 00:18:17.060283	2025-11-16 00:18:17.060283
6	Speaker	Speaker portable untuk acara	2	2	6	t	2025-11-16 00:18:17.060283	2025-11-16 00:18:17.060283
7	Whiteboard	Whiteboard portable	3	3	7	t	2025-11-16 00:18:17.060283	2025-11-16 00:18:17.060283
4	Tripod	Tripod untuk kamera	4	3	4	t	2025-11-16 00:18:17.060283	2025-11-16 00:18:17.060283
2	Proyektor	Proyektor untuk presentasi	3	3	2	t	2025-11-16 00:18:17.060283	2025-11-16 01:25:51.404397
3	Kamera	Kamera DSLR untuk dokumentasi	2	2	3	t	2025-11-16 00:18:17.060283	2025-11-16 01:26:10.449424
1	Laptop	Laptop untuk presentasi dan kerja	5	5	1	t	2025-11-16 00:18:17.060283	2025-11-16 01:25:44.49705
\.


--
-- Data for Name: gallery; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.gallery (id, image, caption, is_active, created_at, updated_at) FROM stdin;
5	69089a6a06743.jpg	Feeding System	t	2025-11-03 19:04:58.027889	2025-11-03 19:04:58.027889
6	69089a7f28fc0.jpg	Neura Measurement	t	2025-11-03 19:05:19.169368	2025-11-03 19:05:19.169368
7	69089a88e7403.jpg	Smart City	t	2025-11-03 19:05:28.949253	2025-11-03 19:05:28.949253
8	69128b9f72a8d.jpg	IOT	t	2025-11-11 08:04:31.471778	2025-11-11 08:04:31.471778
\.


--
-- Data for Name: lecturer_education; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_education (id, team_member_id, institution, degree, field_of_study, start_date, end_date, description, created_at) FROM stdin;
1	4	PENS	S2	Ilmu Mancing Mania	2011-09-21	2012-12-11	Mancing Mania Mantap	2025-11-11 00:31:50.24752
2	5	Hiroshima University	Doctor of Engineering	Information Engineering	2016-05-11	2020-05-11	Learning Engineering Lab	2025-11-11 08:16:27.072312
\.


--
-- Data for Name: lecturer_interests; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_interests (id, team_member_id, interest_text, created_at) FROM stdin;
2	4	Coding	2025-11-11 08:18:58.702908
\.


--
-- Data for Name: lecturer_projects; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_projects (id, team_member_id, title, category, description, start_date, end_date, status, created_at) FROM stdin;
1	4	LMS HIJAU KESAYANGAN	Development	LMS PALING GACOR (KATANYA)	2020-01-15	\N	active	2025-11-11 00:30:38.79273
2	5	Example Projects	Research	Example Description	2025-11-11	2025-11-13	active	2025-11-11 08:14:56.601143
3	4	System Quiz Anti Cheat	Research	Sistem yang digunakan untuk mencegah mahasiswa untuk mencontek saat ujian	2025-11-11	\N	active	2025-11-12 08:52:05.372429
\.


--
-- Data for Name: lecturer_publications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_publications (id, team_member_id, title, year, url, type, created_at) FROM stdin;
1	4	Memancing Dengan Teknik LMS HIJAU	2023		journal	2025-11-11 00:29:49.291628
\.


--
-- Data for Name: lecturer_skills; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.lecturer_skills (id, team_member_id, category, skill_name, skill_icon, proficiency, created_at) FROM stdin;
1	4	Programming	PHP	fab fa-php	3	2025-11-11 00:32:09.613457
\.


--
-- Data for Name: news; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.news (id, title, content, image, author, published_at, is_active, created_at, updated_at) FROM stdin;
1	New Research Project Launched	We are excited to announce our latest research project in AI education...	690aee86c623f.jpg	Admin	2025-11-02 22:58:40.299109	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
3	Product Launch: AI Tutor	Our new AI Tutor Assistant is now available for beta testing...	690af18585084.jpg	Admin	2025-11-02 22:58:40.299109	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	New Release Neura Detection	There is something called neura detection There is something called neura detection There is something called neura detection There is something called neura detection There is something called neura detection There is something called neura detection	690c784b6876c.jpg	Administrator	2025-11-06 17:28:27.430025	t	2025-11-06 17:28:27.430025	2025-11-06 17:28:27.430025
\.


--
-- Data for Name: partners; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.partners (id, name, logo, website, display_order, is_active, created_at, updated_at) FROM stdin;
3	Research Institute	690784163d836.png	https://example.com	3	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
4	Unesa	690790d53181b.png		1	t	2025-11-03 00:09:52.309106	2025-11-03 00:09:52.309106
1	Harvard	690783b52eef8.png	https://example.com	1	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
2	Standfort	690783dc76756.png	https://example.com	1	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
7	Unhas	691139e02264f.png		5	t	2025-11-10 08:03:28.145582	2025-11-10 08:03:28.145582
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products (id, name, description, image, full_description, features, is_active, created_at, updated_at) FROM stdin;
1	Learning Management System	Comprehensive LMS solution for modern education	6908a5433fd62.png	A full-featured learning management system designed for educational institutions.	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
2	PseudoLearn Application	Sebuah media pembelajaran rekonstruksi algoritma pseudocode	6908a6cb2e997.png	Sebuah media pembelajaran rekonstruksi algoritma pseudocode dengan menggunakan pendekatan Element Fill-in-Blank Problems di dalam pemrograman java	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
3	Viat Map Application	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept	6908a58634b35.png	VIAT-map (Visual Arguments Toulmin) Application to help Reding Comprehension by using Toulmin Arguments Concept. We are trying to emphasise the logic behind a written text by adding the claim, ground and warrant following the Toulmin Argument Concept.	\N	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
\.


--
-- Data for Name: research_activities; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.research_activities (id, title, description, image, full_description, is_active, created_at, updated_at) FROM stdin;
2	Virtual Reality Learning	Developing immersive VR experiences for education	690aed549f6de.jpg	Developing AI involves a six-step process: defining a goal, gathering and cleaning data, creating the algorithm, training it, deploying the final product, and monitoring/refining the system. To start, focus on building a foundation in Python, mathematics (like linear algebra and probability), and data structures, then learn essential AI libraries and frameworks such as Scikit-Learn, TensorFlow, and PyTorch. The practical application of these skills through hands-on projects is crucial for success. 	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
1	AI in Education	Exploring the use of artificial intelligence in personalized learning	690af0340c1bc.jpg	Exploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learningExploring the use of artificial intelligence in personalized learning	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
3	Data Analytics	Using big data to improve learning outcomes	research3.jpg	Using big data to improve learning outcomes is always perfect for any needs that require advance research	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109
\.


--
-- Data for Name: rooms; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.rooms (id, name, description, capacity, display_order, is_active, created_at, updated_at) FROM stdin;
1	Ruang Komputer	Lab komputer dengan 30 unit PC	30	1	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
2	Ruang Meeting	Ruang meeting lantai 2	15	2	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
3	Ruang Multimedia	Ruang multimedia dengan proyektor	25	3	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
4	Aula	Aula besar untuk acara	200	4	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
5	Ruang Seminar	Ruang seminar lantai 3	50	5	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
6	Lab Jaringan	Lab untuk praktik jaringan komputer	20	6	t	2025-11-16 00:18:17.068997	2025-11-16 00:18:17.068997
\.


--
-- Data for Name: student_attendance; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.student_attendance (id, nama, nim, attendance_type, attendance_date, attendance_time, created_at, ip_address, user_agent) FROM stdin;
1	Achmad Nabil Afgareza	244107020001	datang	2025-11-12	21:58:29.782164	2025-11-12 21:58:29.782164	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36
2	Achmad Nabil Afgareza	244107020001	pulang	2025-11-12	22:01:40.557765	2025-11-12 22:01:40.557765	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36
3	Qulib Khusit	12345678	datang	2025-11-12	22:02:22.622911	2025-11-12 22:02:22.622911	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36
\.


--
-- Data for Name: team_members; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.team_members (id, name, "position", photo, bio, email, linkedin, display_order, is_active, created_at, updated_at, address, phone, linkedin_url, google_scholar_url, researchgate_url, orcid_url) FROM stdin;
2	Jane Smith	Senior Researcher	69128ab0ca09b.jpeg		jane@inlet.com	https://www.linkedin.com/in/nabilfgrza/	2	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	\N	\N	\N	\N	\N	\N
3	Bob Johnson	Software Engineer	69128acd253eb.jpg	Hi im bob johnson	bob@inlet.com	https://www.linkedin.com/in/nabilfgrza/	3	t	2025-11-02 22:58:40.299109	2025-11-02 22:58:40.299109	\N	\N	\N	\N	\N	\N
5	Banni Satria Andoko	Lead Laboratory	6907822140adb.jpeg	Dosen Magister	pakbanni@mail.co	https://www.linkedin.com/in/nabilfgrza/	0	t	2025-11-02 23:09:05.266806	2025-11-02 23:09:05.266806	Polinema	0813321321	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza
4	Zawaruddin Adullah	Lead Software Engineer	690781f892323.jpg	Aku Basis Data	mrzawa@example.mail	http://www.linkedin.com/in/nabilfgrza	1	t	2025-11-02 23:08:24.600623	2025-11-02 23:08:24.600623	Jl.Boss Besar	81332160514	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza	https://www.linkedin.com/in/nabilfgrza
\.


--
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
10	127.0.0.1	Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0	Local	LOCAL	/inlet-web/index.php	2025-11-10 20:11:47.742361
17	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-12 19:34:47.908024
18	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-12 20:17:02.676808
11	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=lecturer-detail&id=4	2025-11-11 01:18:23.100258
13	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 08:38:28.502299
22	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-16 01:32:05.199532
19	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-12 22:20:36.872909
20	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-14 10:59:54.06802
23	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-17 08:45:20.061283
12	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 06:48:14.455743
9	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/	2025-11-10 21:21:03.030865
14	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 20:13:16.506225
15	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php	2025-11-11 22:08:09.40116
21	::1	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36	Local	LOCAL	/inlet-web/index.php?page=borrowing	2025-11-15 22:55:45.600907
\.


--
-- Name: admin_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.admin_users_id_seq', 1, true);


--
-- Name: borrowings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.borrowings_id_seq', 2, true);


--
-- Name: card_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.card_data_id_seq', 4, true);


--
-- Name: carousel_banners_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.carousel_banners_id_seq', 4, true);


--
-- Name: equipment_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.equipment_id_seq', 7, true);


--
-- Name: gallery_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.gallery_id_seq', 8, true);


--
-- Name: lecturer_education_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_education_id_seq', 2, true);


--
-- Name: lecturer_interests_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_interests_id_seq', 2, true);


--
-- Name: lecturer_projects_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_projects_id_seq', 3, true);


--
-- Name: lecturer_publications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_publications_id_seq', 1, true);


--
-- Name: lecturer_skills_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.lecturer_skills_id_seq', 1, true);


--
-- Name: news_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.news_id_seq', 4, true);


--
-- Name: partners_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.partners_id_seq', 8, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_id_seq', 4, true);


--
-- Name: research_activities_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.research_activities_id_seq', 3, true);


--
-- Name: rooms_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.rooms_id_seq', 6, true);


--
-- Name: student_attendance_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.student_attendance_id_seq', 3, true);


--
-- Name: team_members_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.team_members_id_seq', 6, true);


--
-- Name: visitors_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.visitors_id_seq', 23, true);


--
-- Name: admin_users admin_users_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_email_key UNIQUE (email);


--
-- Name: admin_users admin_users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_pkey PRIMARY KEY (id);


--
-- Name: admin_users admin_users_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin_users
    ADD CONSTRAINT admin_users_username_key UNIQUE (username);


--
-- Name: borrowings borrowings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.borrowings
    ADD CONSTRAINT borrowings_pkey PRIMARY KEY (id);


--
-- Name: card_data card_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.card_data
    ADD CONSTRAINT card_data_pkey PRIMARY KEY (id);


--
-- Name: carousel_banners carousel_banners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.carousel_banners
    ADD CONSTRAINT carousel_banners_pkey PRIMARY KEY (id);


--
-- Name: equipment equipment_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment
    ADD CONSTRAINT equipment_name_key UNIQUE (name);


--
-- Name: equipment equipment_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.equipment
    ADD CONSTRAINT equipment_pkey PRIMARY KEY (id);


--
-- Name: gallery gallery_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.gallery
    ADD CONSTRAINT gallery_pkey PRIMARY KEY (id);


--
-- Name: lecturer_education lecturer_education_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education
    ADD CONSTRAINT lecturer_education_pkey PRIMARY KEY (id);


--
-- Name: lecturer_interests lecturer_interests_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests
    ADD CONSTRAINT lecturer_interests_pkey PRIMARY KEY (id);


--
-- Name: lecturer_projects lecturer_projects_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects
    ADD CONSTRAINT lecturer_projects_pkey PRIMARY KEY (id);


--
-- Name: lecturer_publications lecturer_publications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications
    ADD CONSTRAINT lecturer_publications_pkey PRIMARY KEY (id);


--
-- Name: lecturer_skills lecturer_skills_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills
    ADD CONSTRAINT lecturer_skills_pkey PRIMARY KEY (id);


--
-- Name: news news_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.news
    ADD CONSTRAINT news_pkey PRIMARY KEY (id);


--
-- Name: partners partners_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.partners
    ADD CONSTRAINT partners_pkey PRIMARY KEY (id);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: research_activities research_activities_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.research_activities
    ADD CONSTRAINT research_activities_pkey PRIMARY KEY (id);


--
-- Name: rooms rooms_name_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_name_key UNIQUE (name);


--
-- Name: rooms rooms_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.rooms
    ADD CONSTRAINT rooms_pkey PRIMARY KEY (id);


--
-- Name: student_attendance student_attendance_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT student_attendance_pkey PRIMARY KEY (id);


--
-- Name: team_members team_members_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.team_members
    ADD CONSTRAINT team_members_pkey PRIMARY KEY (id);


--
-- Name: student_attendance unique_attendance; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.student_attendance
    ADD CONSTRAINT unique_attendance UNIQUE (nim, attendance_date, attendance_type);


--
-- Name: visitors visitors_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.visitors
    ADD CONSTRAINT visitors_pkey PRIMARY KEY (id);


--
-- Name: idx_attendance_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_date ON public.student_attendance USING btree (attendance_date DESC);


--
-- Name: idx_attendance_nim; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_nim ON public.student_attendance USING btree (nim);


--
-- Name: idx_attendance_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_attendance_type ON public.student_attendance USING btree (attendance_type);


--
-- Name: idx_borrowings_dates; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_dates ON public.borrowings USING btree (borrow_date, return_date);


--
-- Name: idx_borrowings_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_status ON public.borrowings USING btree (status);


--
-- Name: idx_borrowings_type; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_borrowings_type ON public.borrowings USING btree (type);


--
-- Name: idx_equipment_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_equipment_active ON public.equipment USING btree (is_active);


--
-- Name: idx_ip_date; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_ip_date ON public.visitors USING btree (ip_address, visited_at);


--
-- Name: idx_lecturer_education_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_education_member ON public.lecturer_education USING btree (team_member_id);


--
-- Name: idx_lecturer_interests_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_interests_member ON public.lecturer_interests USING btree (team_member_id);


--
-- Name: idx_lecturer_projects_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_projects_member ON public.lecturer_projects USING btree (team_member_id);


--
-- Name: idx_lecturer_publications_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_publications_member ON public.lecturer_publications USING btree (team_member_id);


--
-- Name: idx_lecturer_skills_member; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_lecturer_skills_member ON public.lecturer_skills USING btree (team_member_id);


--
-- Name: idx_rooms_active; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_rooms_active ON public.rooms USING btree (is_active);


--
-- Name: lecturer_education lecturer_education_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_education
    ADD CONSTRAINT lecturer_education_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- Name: lecturer_interests lecturer_interests_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_interests
    ADD CONSTRAINT lecturer_interests_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- Name: lecturer_projects lecturer_projects_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_projects
    ADD CONSTRAINT lecturer_projects_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- Name: lecturer_publications lecturer_publications_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_publications
    ADD CONSTRAINT lecturer_publications_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- Name: lecturer_skills lecturer_skills_team_member_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.lecturer_skills
    ADD CONSTRAINT lecturer_skills_team_member_id_fkey FOREIGN KEY (team_member_id) REFERENCES public.team_members(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict XVgYYenCdhBEeho1Mo1oaOuhXFv2E2i1Sfc1e8wFC9JuVygJzKEs5XtzhIYEBDE

