prompt PL/SQL Developer import file
prompt Created on 4 Март 2018 г. by Dexp
set feedback off
set define off
prompt Dropping CALL_TYPE...
drop table CALL_TYPE cascade constraints;
prompt Dropping DEPARTAMENTS...
drop table DEPARTAMENTS cascade constraints;
prompt Dropping SERVICES...
drop table SERVICES cascade constraints;
prompt Dropping SOURCE_AUTO...
drop table SOURCE_AUTO cascade constraints;
prompt Dropping SOURCE_MAN...
drop table SOURCE_MAN cascade constraints;
prompt Dropping ACCESS_DEP...
drop table ACCESS_DEP cascade constraints;
prompt Dropping CALL_THEME...
drop table CALL_THEME cascade constraints;
prompt Dropping MED_STATUS...
drop table MED_STATUS cascade constraints;
prompt Dropping SOURCE_MAN_DETAIL...
drop table SOURCE_MAN_DETAIL cascade constraints;
prompt Dropping ROLES...
drop table ROLES cascade constraints;
prompt Dropping USERS...
drop table USERS cascade constraints;
prompt Dropping CALL_BASE...
drop table CALL_BASE cascade constraints;
prompt Dropping HOSPITALS...
drop table HOSPITALS cascade constraints;
prompt Dropping CALL_BASE_CLINIC...
drop table CALL_BASE_CLINIC cascade constraints;
prompt Dropping CALL_BASE_HIST...
drop table CALL_BASE_HIST cascade constraints;
prompt Dropping SUBWAYS...
drop table SUBWAYS cascade constraints;
prompt Dropping USER_DEP_ALLOC...
drop table USER_DEP_ALLOC cascade constraints;
prompt Creating CALL_TYPE...
create table CALL_TYPE
(
  id   NUMBER not null,
  name VARCHAR2(200)
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_TYPE
  add constraint PK_CALL_TYPE primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating DEPARTAMENTS...
create table DEPARTAMENTS
(
  id      NUMBER not null,
  name    VARCHAR2(200),
  deleted DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table DEPARTAMENTS
  add constraint PK_DEPARTAMENTS primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table DEPARTAMENTS
  add constraint UK_DEPARTAMENTS unique (NAME, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating SERVICES...
create table SERVICES
(
  id      NUMBER not null,
  name    VARCHAR2(200) not null,
  deleted DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SERVICES
  add constraint PK_SERVICES primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SERVICES
  add constraint UK_SERVICES unique (NAME, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating SOURCE_AUTO...
create table SOURCE_AUTO
(
  id      NUMBER not null,
  bnumber VARCHAR2(200) not null,
  name    VARCHAR2(200) not null,
  deleted DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_AUTO
  add constraint PK_SOURCE_AUTO primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_AUTO
  add constraint UK_SOURCE_AUTO unique (BNUMBER, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating SOURCE_MAN...
create table SOURCE_MAN
(
  id       NUMBER not null,
  name     VARCHAR2(200) not null,
  detail   VARCHAR2(200),
  priority VARCHAR2(200),
  deleted  DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_MAN
  add constraint PK_SOURCE_MAN primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_MAN
  add constraint UK_SOURCE_MAN unique (NAME, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating ACCESS_DEP...
create table ACCESS_DEP
(
  departament_id NUMBER not null,
  source_auto_id NUMBER,
  source_man_id  NUMBER,
  call_type_id   NUMBER,
  service_id     NUMBER
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table ACCESS_DEP
  add constraint FK_DEP_ACC1 foreign key (DEPARTAMENT_ID)
  references DEPARTAMENTS (ID) on delete set null;
alter table ACCESS_DEP
  add constraint FK_DEP_ACC2 foreign key (SOURCE_AUTO_ID)
  references SOURCE_AUTO (ID) on delete set null;
alter table ACCESS_DEP
  add constraint FK_DEP_ACC3 foreign key (SOURCE_MAN_ID)
  references SOURCE_MAN (ID) on delete set null;
alter table ACCESS_DEP
  add constraint FK_DEP_ACC4 foreign key (CALL_TYPE_ID)
  references CALL_TYPE (ID) on delete set null;
alter table ACCESS_DEP
  add constraint FK_DEP_ACC5 foreign key (SERVICE_ID)
  references SERVICES (ID) on delete set null;

prompt Creating CALL_THEME...
create table CALL_THEME
(
  id      NUMBER not null,
  name    VARCHAR2(200) not null,
  target  NUMBER not null,
  deleted DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_THEME
  add constraint PK_CALL_THEME primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_THEME
  add constraint UK_CALL_THEME unique (NAME)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating MED_STATUS...
create table MED_STATUS
(
  id    NUMBER not null,
  name  VARCHAR2(200) not null,
  color VARCHAR2(200)
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table MED_STATUS
  add constraint PK_MED_STATUS primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table MED_STATUS
  add constraint UK_MED_STATUS unique (NAME)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating SOURCE_MAN_DETAIL...
create table SOURCE_MAN_DETAIL
(
  id            NUMBER not null,
  source_man_id NUMBER not null,
  name          VARCHAR2(200) not null,
  deleted       DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_MAN_DETAIL
  add constraint PK_SOURCE_MAN_DET primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_MAN_DETAIL
  add constraint UK_SOURCE_MAN_DET unique (SOURCE_MAN_ID, NAME, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SOURCE_MAN_DETAIL
  add constraint FK_SOURCE_MAN_DET foreign key (SOURCE_MAN_ID)
  references SOURCE_MAN (ID) on delete cascade;

prompt Creating ROLES...
create table ROLES
(
  id   NUMBER not null,
  name VARCHAR2(200) not null
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table ROLES
  add constraint PK_ROLES primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table ROLES
  add constraint UK_ROLES unique (NAME)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating USERS...
create table USERS
(
  id       NUMBER not null,
  login    VARCHAR2(200) not null,
  password VARCHAR2(200) not null,
  fio      VARCHAR2(200) not null,
  role_id  NUMBER default 4 not null,
  activity DATE,
  deleted  DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USERS
  add constraint PK_USERS primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USERS
  add constraint UK_USERS unique (LOGIN, PASSWORD, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USERS
  add constraint FK_USERS foreign key (ROLE_ID)
  references ROLES (ID);

prompt Creating CALL_BASE...
create table CALL_BASE
(
  id                NUMBER not null,
  date_call         DATE not null,
  anumber           VARCHAR2(200) not null,
  bnumber           VARCHAR2(200) not null,
  sc_agid           VARCHAR2(200) not null,
  sc_call_id        NUMBER not null,
  sc_project_id     NUMBER not null,
  call_theme_id     NUMBER not null,
  source_auto_id    NUMBER not null,
  source_man_id     NUMBER not null,
  call_type_id      NUMBER not null,
  service_id        NUMBER not null,
  source_man_det_id NUMBER,
  result_id         NUMBER,
  result_det        NUMBER,
  fio_id            NUMBER,
  status_id         NUMBER not null,
  call_back_date    DATE,
  call_back_num     NUMBER,
  transfer_num      VARCHAR2(32),
  client_name       VARCHAR2(200),
  phone_mob         VARCHAR2(200),
  phone_home        VARCHAR2(200),
  email             VARCHAR2(200),
  age               NUMBER,
  comments          VARCHAR2(200),
  last_change       DATE,
  date_close        DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
create index IDX_CALL_BASE_ACCESS on CALL_BASE (SOURCE_AUTO_ID, SOURCE_MAN_ID, CALL_TYPE_ID, SERVICE_ID)
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE
  add constraint PK_CALL_BASE primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE
  add constraint UK_TRANSFER_NUM unique (TRANSFER_NUM)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE
  add constraint FK_CALL_BASE1 foreign key (SOURCE_AUTO_ID)
  references SOURCE_AUTO (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE2 foreign key (SOURCE_MAN_ID)
  references SOURCE_MAN (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE3 foreign key (CALL_TYPE_ID)
  references CALL_TYPE (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE4 foreign key (SERVICE_ID)
  references SERVICES (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE5 foreign key (SOURCE_MAN_DET_ID)
  references SOURCE_MAN_DETAIL (ID) on delete set null
  disable;
alter table CALL_BASE
  add constraint FK_CALL_BASE6 foreign key (FIO_ID)
  references USERS (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE7 foreign key (STATUS_ID)
  references MED_STATUS (ID) on delete set null;
alter table CALL_BASE
  add constraint FK_CALL_BASE8 foreign key (CALL_THEME_ID)
  references CALL_THEME (ID) on delete set null;

prompt Creating HOSPITALS...
create table HOSPITALS
(
  id         NUMBER not null,
  name       VARCHAR2(64) not null,
  service_id NUMBER not null,
  city       VARCHAR2(64),
  address    VARCHAR2(200),
  phone      VARCHAR2(64),
  manager_id NUMBER,
  trademark  VARCHAR2(64)
  deleted    DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table HOSPITALS
  add constraint PK_HOSPITALS primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table HOSPITALS
  add constraint UK_HOSPITALS unique (NAME, SERVICE_ID, DELETED)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table HOSPITALS
  add constraint FK_HOSPITALS foreign key (SERVICE_ID)
  references SERVICES (ID) on delete set null;
alter table HOSPITALS
  add constraint FK_HOSPITALS1 foreign key (MANAGER_ID)
  references USERS (ID) on delete set null;

prompt Creating CALL_BASE_CLINIC...
create table CALL_BASE_CLINIC
(
  id          NUMBER not null,
  base_id     NUMBER not null,
  hospital_id NUMBER not null,
  client_name VARCHAR2(200) not null,
  age         NUMBER
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE_CLINIC
  add constraint PK_CALL_BASE_CLINIC primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE_CLINIC
  add constraint FK_CALL_BASE_CLINIC foreign key (BASE_ID)
  references CALL_BASE (ID);
alter table CALL_BASE_CLINIC
  add constraint FK_CALL_BASE_CLINIC1 foreign key (HOSPITAL_ID)
  references HOSPITALS (ID);

prompt Creating CALL_BASE_HIST...
create table CALL_BASE_HIST
(
  id        NUMBER not null,
  base_id   NUMBER not null,
  date_det  DATE not null,
  status_id NUMBER not null,
  comments  VARCHAR2(200),
  operator  VARCHAR2(200),
  user_id   NUMBER
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE_HIST
  add constraint PK_CALL_BASE_HIST primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table CALL_BASE_HIST
  add constraint FK_CALL_BASE_HIST foreign key (BASE_ID)
  references CALL_BASE (ID) on delete set null;
alter table CALL_BASE_HIST
  add constraint FK_CALL_BASE_HIST1 foreign key (STATUS_ID)
  references MED_STATUS (ID) on delete set null;
alter table CALL_BASE_HIST
  add constraint FK_CALL_BASE_HIST2 foreign key (USER_ID)
  references USERS (ID) on delete set null;

prompt Creating SUBWAYS...
create table SUBWAYS
(
  id   NUMBER not null,
  name VARCHAR2(64) not null,
  city NUMBER not null
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SUBWAYS
  add constraint PK_SUBWAY primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table SUBWAYS
  add constraint UK_SUBWAY unique (NAME, CITY)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );

prompt Creating USER_DEP_ALLOC...
create table USER_DEP_ALLOC
(
  id      NUMBER not null,
  user_id NUMBER not null,
  dep_id  NUMBER not null,
  deleted DATE
)
tablespace SC
  pctfree 10
  initrans 1
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USER_DEP_ALLOC
  add constraint PK_USER_DEP_ALLOC primary key (ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USER_DEP_ALLOC
  add constraint UK_USER_DEP unique (USER_ID, DEP_ID)
  using index 
  tablespace SC
  pctfree 10
  initrans 2
  maxtrans 255
  storage
  (
    initial 64K
    next 1M
    minextents 1
    maxextents unlimited
  );
alter table USER_DEP_ALLOC
  add constraint FK_USER_DEP_1 foreign key (USER_ID)
  references USERS (ID) on delete cascade;
alter table USER_DEP_ALLOC
  add constraint FK_USER_DEP_2 foreign key (DEP_ID)
  references DEPARTAMENTS (ID) on delete cascade;

prompt Disabling triggers for CALL_TYPE...
alter table CALL_TYPE disable all triggers;
prompt Disabling triggers for DEPARTAMENTS...
alter table DEPARTAMENTS disable all triggers;
prompt Disabling triggers for SERVICES...
alter table SERVICES disable all triggers;
prompt Disabling triggers for SOURCE_AUTO...
alter table SOURCE_AUTO disable all triggers;
prompt Disabling triggers for SOURCE_MAN...
alter table SOURCE_MAN disable all triggers;
prompt Disabling triggers for ACCESS_DEP...
alter table ACCESS_DEP disable all triggers;
prompt Disabling triggers for CALL_THEME...
alter table CALL_THEME disable all triggers;
prompt Disabling triggers for MED_STATUS...
alter table MED_STATUS disable all triggers;
prompt Disabling triggers for SOURCE_MAN_DETAIL...
alter table SOURCE_MAN_DETAIL disable all triggers;
prompt Disabling triggers for ROLES...
alter table ROLES disable all triggers;
prompt Disabling triggers for USERS...
alter table USERS disable all triggers;
prompt Disabling triggers for CALL_BASE...
alter table CALL_BASE disable all triggers;
prompt Disabling triggers for HOSPITALS...
alter table HOSPITALS disable all triggers;
prompt Disabling triggers for CALL_BASE_CLINIC...
alter table CALL_BASE_CLINIC disable all triggers;
prompt Disabling triggers for CALL_BASE_HIST...
alter table CALL_BASE_HIST disable all triggers;
prompt Disabling triggers for SUBWAYS...
alter table SUBWAYS disable all triggers;
prompt Disabling triggers for USER_DEP_ALLOC...
alter table USER_DEP_ALLOC disable all triggers;
prompt Disabling foreign key constraints for ACCESS_DEP...
alter table ACCESS_DEP disable constraint FK_DEP_ACC1;
alter table ACCESS_DEP disable constraint FK_DEP_ACC2;
alter table ACCESS_DEP disable constraint FK_DEP_ACC3;
alter table ACCESS_DEP disable constraint FK_DEP_ACC4;
alter table ACCESS_DEP disable constraint FK_DEP_ACC5;
prompt Disabling foreign key constraints for SOURCE_MAN_DETAIL...
alter table SOURCE_MAN_DETAIL disable constraint FK_SOURCE_MAN_DET;
prompt Disabling foreign key constraints for USERS...
alter table USERS disable constraint FK_USERS;
prompt Disabling foreign key constraints for CALL_BASE...
alter table CALL_BASE disable constraint FK_CALL_BASE1;
alter table CALL_BASE disable constraint FK_CALL_BASE2;
alter table CALL_BASE disable constraint FK_CALL_BASE3;
alter table CALL_BASE disable constraint FK_CALL_BASE4;
alter table CALL_BASE disable constraint FK_CALL_BASE6;
alter table CALL_BASE disable constraint FK_CALL_BASE7;
alter table CALL_BASE disable constraint FK_CALL_BASE8;
prompt Disabling foreign key constraints for HOSPITALS...
alter table HOSPITALS disable constraint FK_HOSPITALS;
alter table HOSPITALS disable constraint FK_HOSPITALS1;
prompt Disabling foreign key constraints for CALL_BASE_CLINIC...
alter table CALL_BASE_CLINIC disable constraint FK_CALL_BASE_CLINIC;
alter table CALL_BASE_CLINIC disable constraint FK_CALL_BASE_CLINIC1;
prompt Disabling foreign key constraints for CALL_BASE_HIST...
alter table CALL_BASE_HIST disable constraint FK_CALL_BASE_HIST;
alter table CALL_BASE_HIST disable constraint FK_CALL_BASE_HIST1;
alter table CALL_BASE_HIST disable constraint FK_CALL_BASE_HIST2;
prompt Disabling foreign key constraints for USER_DEP_ALLOC...
alter table USER_DEP_ALLOC disable constraint FK_USER_DEP_1;
alter table USER_DEP_ALLOC disable constraint FK_USER_DEP_2;
prompt Loading CALL_TYPE...
insert into CALL_TYPE (id, name)
values (-1, 'all');
insert into CALL_TYPE (id, name)
values (1, 'Первичный');
insert into CALL_TYPE (id, name)
values (2, 'Повторный');
commit;
prompt 3 records loaded
prompt Loading DEPARTAMENTS...
insert into DEPARTAMENTS (id, name, deleted)
values (-1, 'all', null);
insert into DEPARTAMENTS (id, name, deleted)
values (1, 'Администрация', null);
insert into DEPARTAMENTS (id, name, deleted)
values (2, 'Бухгалтерия', to_date('21-02-2018 11:23:15', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (3, 'Тестировщики', to_date('21-02-2018 11:23:07', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (4, 'fgdfgf', to_date('24-01-2018 00:59:17', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (5, 'Колл-центр', null);
insert into DEPARTAMENTS (id, name, deleted)
values (6, 'Косметологи', null);
insert into DEPARTAMENTS (id, name, deleted)
values (7, 'Стоматологи', null);
insert into DEPARTAMENTS (id, name, deleted)
values (8, 'Трихологи', null);
insert into DEPARTAMENTS (id, name, deleted)
values (9, 'Пластики', null);
insert into DEPARTAMENTS (id, name, deleted)
values (10, 'Гинекологи', null);
commit;
prompt 11 records loaded
prompt Loading SERVICES...
insert into SERVICES (id, name, deleted)
values (-1, 'all', null);
insert into SERVICES (id, name, deleted)
values (1, 'Стоматология', null);
insert into SERVICES (id, name, deleted)
values (2, 'Косметология', null);
insert into SERVICES (id, name, deleted)
values (3, 'Гинекология', null);
insert into SERVICES (id, name, deleted)
values (4, 'Пластика', null);
insert into SERVICES (id, name, deleted)
values (5, 'Трихология', null);
insert into SERVICES (id, name, deleted)
values (6, 'Мишлен', null);
commit;
prompt 7 records loaded
prompt Loading SOURCE_AUTO...
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (-1, 'all', 'all', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (112, '5555555555', 'Источник (Авто) ТЕСТ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (106, '5555555557', 'Тестовый источник рекламы для проверки сценария', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (6, '2290101', 'Наш - Москва - Тест', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (7, '2290126', 'Наш - Москва - Мини-каталог Стоматология+Новоклиник', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (8, '2290128', 'ДМТ - сайт - www.slimclinic.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (9, '2290136', 'ДМТ - Москва - www.contact-center.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (10, '2290145', 'ДМТ - Москва - www.vse-svoi.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (11, '4959671943', 'Наш - Москва - Сайт Медиа Лиды пластика', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (12, '4959671946', 'ДМТ - сайт - www.novoclinic-msk.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (13, '4959671948', 'Наш - сайт - www.novoklinik.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (14, '4959849601', 'ДМТ - Москва контекст-1 - контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (15, '4959849603', 'Наш - Питер - Питер Банер Стоматология', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (16, '4959849604', 'ДМТ - Москва - Вилстрим ремаркетинг в Google Adwords ДМТ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (17, '4959849606', 'Наш - Москва - Медиагуру для подмены номера', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (18, '4959849610', 'ДМТ - Москва - Контакт-центр ремаркетинг в Google Adwords ДМТ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (19, '4959849612', 'ДМТ - Москва - Wilstream контекст ДМТ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (20, '4959849614', 'Наш - Москва - Лиды Перфекто Пластика (грудь и нос)', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (21, '4959849617', 'ДМТ - контекст - Слимклиник-контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (22, '4959849623', 'ДМТ - Москва - Пластика контекст сайт: molodeu context plastica', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (23, '4959849624', 'Наш - Москва - Вилстрим Авито', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (24, '4959849628', 'ДМТ - Москва - ДМТ Google Adwords на Новоклинике', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (25, '4959849631', 'Наш - Москва - Лиды косметология Риф', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (26, '4992722761', 'ДМТ - Нижний Новгород - https://vse-svoi.ru/nn/', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (27, '4992722767', 'Наш - Москва - Перфекто Лиды стоматология Питер', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (28, '4992722772', 'Наш - сайт НН (номер в тв рекламе) - http://www.7894444.ru/?ref_city=nn', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (29, '4993700262', 'Наш - Москва - Перфекто Лиды стоматология Москва', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (30, '4993700268', 'ДМТ - Москва - Хочу детей кол-трекинг', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (31, '4993700279', 'Наш - Москва - Бухгалтерия Умные технологии', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (32, '4993700297', 'Наш - Москва - Трудоустрйство Россельхоз, РЖД', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (33, '4993700299', 'Наш - Москва - Саша Куц Лиды Сиськи mammoplastik.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (34, '4999511741', 'ДМТ - Питер  - https://vse-svoi.ru/spb/', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (35, '4999511742', 'Наш - Москва - Лидокол пластика +трихология', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (36, '4999511743', 'Наш - сайт Питер - http://www.7894444.ru/?ref_city=spb', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (37, '4999511747', 'ДМТ - Питер контекст - контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (38, '4999511749', 'Наш - Москва - Тех поддержка стоматологий', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (39, '4999955182', 'Наш - Москва - Сайт Медиа Лиды стоматология Москва', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (40, '4999955183', 'Наш - Москва - Сайт Медиа Лиды стоматология Питер', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (41, '74950110561', 'Наш - Москва - Digital G Питер', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (42, '74950110562', 'Наш - Москва - Digital G Москва', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (43, '74950110568', 'Наш - Москва - Лиды Абдоминопластика А.Куц', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (44, '74950110571', 'Наш - Москва - IT Planet Лиды стоматология Москва', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (45, '74950110572', 'Наш - Москва - IT Plane Лиды стоматология Питер', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (46, '74950110574', 'Наш - Москва - IT Plane Лиды пластика', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (47, '74950110581', 'Наш - Москва - Лиды Ушкар Михаил Питер', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (48, '74950110586', 'Наш - Москва - Клон 7894444 vse-svoi.moscow', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (49, '74950110589', 'Наш  - Москва - Ринопластика Лиды Александр Куц -1', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (50, '74950110591', 'Наш - Москва - Михаил Задумов Продвижение Лиды стом М', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (51, '74950110593', 'Наш - Москва - Михаил Задумов Продвижение Лиды стом П', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (52, '74950110596', 'Наш - Москва - Михаил Задумов Продвижение Лиды пластика', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (53, '74950110597', 'Наш  - Москва - Лиды Ушкар Михаил Москва', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (54, '7850303', 'Наш - Москва - Стоматология Тест Радио, транспорт', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (55, '7862120', 'Наш - Москва - купи купон', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (56, '7883478', 'Наш - Москва - Смарт Лайн ИПХ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (57, '7883482', 'Наш  - Питер - Стоматология Питер Лиды Александр Куц', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (58, '7883485', 'Наш - Москва - 2Gis стоматология', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (59, '7883494', 'Наш - cайт - www.mishilen-detox.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (60, '7883508', 'ДМТ - Нижний Новгород контекст  - контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (61, '7883510', 'Наш - Москва - Трудоустройство', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (62, '7883524', 'Кокос - Москва - Кокос www.novoclinic.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (63, '7883525', 'Наш - Москва - Яндекс-Медицина', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (64, '7883529', 'Наш - Москва - трудоустройство медицина', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (65, '7883532', 'Наш - Москва - Лидокол стом М', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (66, '7883540', 'ДМТ - сайт - www.sochidetox.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (67, '7883543', 'Наш - Москва - ZOON новоклиник', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (68, '7883552', 'Наш - Москва - Лидокол стом П', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (69, '7883554', 'Кокос - Москва - Кокос www.wilstream.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (70, '7883558', 'ДМТ - Москва - www.wilstream-msk.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (71, '7883559', 'ДМТ - контекст - Новоклиник-контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (72, '7883560', 'Наш - Москва - Смарт Лайн Новоклиник ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (73, '7883565', 'Наш - Москва - ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (74, '7883571', 'Наш - Москва - 2Gis новоклиник', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (75, '7883586', 'ДМТ - Москва - МЖЦ контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (76, '7883590', 'Наш - Москва - смс рассылка трихология', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (77, '7883591', 'ДМТ - контекст - доктор волос контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (78, '7883593', 'Кокос - Москва - Кокос www.doktorvolos.ru ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (79, '7883595', 'Наш - Москва - Визитки для сотрудников', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (80, '7883597', 'Наш - Москва - Визитки для сотрудников-2', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (81, '7883598', 'Наш - Москва - ZOON стоматология', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (82, '7884982', 'Наш - сайт НН (номер в тв рекламе) - http://www.7894444.ru/?ref_city=nn', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (83, '7884984', 'ДМТ - сайт - www.hochudetey.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (84, '7884986', 'Наш - Москва - Бухгалтерия Юмакс', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (85, '7884988', 'Наш - Москва - Купибонус ИПХ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (86, '7884989', 'ДМТ - сайт - www.doktorvolos-msk.ru ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (87, '7885801', 'ДМТ - сайт - www.molodeu.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (88, '7885802', 'Наш - Москва - Инстаграм Лилия Все Свои Митино ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (89, '7885803', 'Наш - Москва - Инстаграм Лилия (К.Дельник) Новоклиник Беляево @novoklinik.estetika', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (90, '7885806', 'Наш - сайт - www.institutbeauty.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (91, '7885808', 'ДМТ - Москва контекст - контекст', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (92, '7885810', 'ДМТ - Москва - ДМТ контекст пластика Лиды', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (93, '7885814', 'Наш - cайт - www.vsegdakrasiva.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (94, '7885819', 'Наш - панфилова 18.03.15  - Смарт Лайн Все Свои', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (95, '7885833', 'Наш - смс - Мишлен', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (96, '7885837', 'Наш - Москва - Лиды нос и грудь Александра', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (97, '7885840', 'ДМТ - сайт - www.medzhencentre.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (98, '7885845', 'Наш - сайт Москва - www.7894444.ru www.elenshleger.ru www.terezanov.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (99, '7885847', 'Наш - Москва - Смарт лайн Доктор Волос', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (100, '7885848', 'Наш - Москва - "Центр красоты" ЦБ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (101, '8312613335', 'Наш - НН - Бегущая строка НН ТВ реклама', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (108, '74950110621', 'Тест 74950110621', to_date('27-02-2018 14:31:54', 'dd-mm-yyyy hh24:mi:ss'));
commit;
prompt 100 records committed...
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (111, '74950110621', 'Наш - Смс-таргет-Билайн', null);
commit;
prompt 101 records loaded
prompt Loading SOURCE_MAN...
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (-1, 'all', null, 'all', '0');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (1, 'Листовка в почтовый ящик', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (2, 'Мини-каталог', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (3, 'Интернет', null, 'Веб сайт', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (4, 'Телевизор', null, 'Телеканал', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (5, 'Листовка у метро', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (6, 'Листовка под дворник/ручку авто', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (7, 'Объявление в подъезде/лифте', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (8, 'Остановки', null, 'Станция метро', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (9, 'Купон', null, 'Купон', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (10, 'ЖД станции', null, 'ЖД станция', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (11, 'Подарочный сертификат', null, 'Сертификат', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (12, 'Reserved1', to_date('11-02-2018 15:52:55', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (13, 'Reserved2', to_date('11-02-2018 15:52:58', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (14, 'Reserved3', to_date('11-02-2018 15:53:03', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (15, 'Reserved4', to_date('11-02-2018 15:53:08', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (16, 'Reserved5', to_date('11-02-2018 15:53:13', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (17, 'Reserved6', to_date('11-02-2018 15:53:19', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (18, 'Reserved7', to_date('11-02-2018 15:53:25', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (19, 'Reserved8', to_date('11-02-2018 15:53:30', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (20, '2Gis', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (21, 'SMS рассылка', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (22, 'Газета в почтовом ящике', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (23, 'Газета в пробке', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (24, 'Живёт рядом', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (25, 'Календарь', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (26, 'Листовка в пробке', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (27, 'Наклейка в метро', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (28, 'Подарочная карта', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (29, 'Радио', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (30, 'Ранее лечился в клинике', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (31, 'Реклама в маршрутке', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (32, 'Рекламный щит', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (33, 'Рекомендации', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (34, 'Reserved9', to_date('11-02-2018 16:18:10', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (35, 'Reserved10', to_date('11-02-2018 16:17:54', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (36, 'Reserved11', to_date('11-02-2018 16:18:00', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (37, 'Reserved12', to_date('11-02-2018 16:18:05', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (38, 'Другое', null, null, '99');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (39, 'Не помнит', null, null, '99');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (40, 'Везде', to_date('13-02-2018', 'dd-mm-yyyy'), null, '100');
commit;
prompt 41 records loaded
prompt Loading ACCESS_DEP...
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (10, -1, -1, -1, 3);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (6, -1, -1, -1, 2);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (1, -1, -1, -1, -1);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (2, -1, -1, -1, 2);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (7, -1, -1, -1, 1);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (8, -1, -1, -1, 5);
insert into ACCESS_DEP (departament_id, source_auto_id, source_man_id, call_type_id, service_id)
values (9, -1, -1, -1, 4);
commit;
prompt 7 records loaded
prompt Loading CALL_THEME...
insert into CALL_THEME (id, name, target, deleted)
values (1, 'Медицинские услуги', 1, null);
insert into CALL_THEME (id, name, target, deleted)
values (2, 'Информация для руководства', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (3, 'Собеседование в клинику', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (4, 'Анализы', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (5, 'Трудоустройство промоутер', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (6, 'Невыход на работу', 0, null);
commit;
prompt 6 records loaded
prompt Loading MED_STATUS...
insert into MED_STATUS (id, name, color)
values (1, 'Новый', 'black');
insert into MED_STATUS (id, name, color)
values (2, 'Назначено', 'green');
insert into MED_STATUS (id, name, color)
values (3, 'Перезвонить', 'blue');
insert into MED_STATUS (id, name, color)
values (4, 'Недозвон', 'orange');
insert into MED_STATUS (id, name, color)
values (5, 'Глухой недозвон', 'orangered');
insert into MED_STATUS (id, name, color)
values (6, 'Запись в клинику', '#e626cd');
insert into MED_STATUS (id, name, color)
values (7, 'Отказ/Негатив', 'magenta');
insert into MED_STATUS (id, name, color)
values (8, 'Ошибка', 'red');
insert into MED_STATUS (id, name, color)
values (9, 'Closed', 'cyan');
commit;
prompt 9 records loaded
prompt Loading SOURCE_MAN_DETAIL...
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (514, 3, 'Facebook', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (513, 3, 'Instagram', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (516, 3, 'Vkontakte', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (517, 3, 'Zoon.ru', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (506, 4, 'Культура', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (509, 4, 'НТВ', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (520, 9, 'Выгода', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (518, 9, 'Купи-бонус', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (519, 9, 'Купи-купон', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (526, 10, 'Бескудниково', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (527, 10, 'Дегунино', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (525, 10, 'Каланчевская', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (529, 10, 'Косино', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (528, 10, 'Лианозово', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (523, 10, 'Лось', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (522, 10, 'Люберцы', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (521, 10, 'Матвеевская', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (530, 10, 'Питер-Удельная', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (524, 10, 'Рижская', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (531, 29, 'Серебряный дождь', null);
commit;
prompt 20 records loaded
prompt Loading ROLES...
insert into ROLES (id, name)
values (1, 'Администратор');
insert into ROLES (id, name)
values (2, 'Супервайзер');
insert into ROLES (id, name)
values (3, 'Обозреватель');
insert into ROLES (id, name)
values (4, 'Оператор');
commit;
prompt 4 records loaded
prompt Loading USERS...
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (1, 'admin', 'admin', 'admin', null, 1, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (2, 'loginin', 'passwsw', 'Пользователь', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (3, 'nablu', 'bluna', 'Страший обозреватель', null, 3, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (4, 'super', 'visor', 'Супервизор', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (5, 'stom', 'stom', 'Стоматолог', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (6, 'plast', 'plast', 'Пластик', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (7, 'gin', 'gin', 'Гинеколог', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (8, 'kosm', 'kosm', 'Косметолог', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (9, 'trix', 'trix', 'Трихолог', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (10, 'асоколова', 'ас9ва', 'Соколова Анна', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (11, 'оалибекова', 'оа10ва', 'Алибекова Оксана', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (12, 'Zhanna', 'Alibekova', 'Алибекова Жанна', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (13, 'Olga', 'Lukiamchuk', 'Лукьямчук Ольга', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (14, 'тзубкова', 'тз8ва', 'Зубкова Татьяна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (15, 'очехова', 'оч7ва', 'Чехова Оксана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (16, 'сногай', 'сн6ай', 'Ногай Светлана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (17, 'ивый', 'ив4ый', 'Вый Ирина', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (18, 'вбойченко', 'вб9ко', 'Бойченко Виолета', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (19, 'мбычкова', 'мб8ва', 'Бычкова Марина', null, 4, to_date('02-03-2018 09:55:09', 'dd-mm-yyyy hh24:mi:ss'));
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (20, 'нмубаракшина', 'нм12на', 'Мубаракшина Нелли', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (21, 'сзубарева', 'сз9ва', 'Зубарева Светлана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (22, 'абелова', 'аб7ва', 'Белова Анастасия', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (23, 'есмирнова', 'ес9ва', 'Смирнова Елена', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (24, 'огнидина', 'ог8на', 'Гнидина Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (25, 'ирудакова', 'ир9ва', 'Рудакова Ирина', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (26, 'емельникова', 'ем11ва', 'Мельникова Елена', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (27, 'кгордиенко', 'кг10ко', 'Гордиенко Катя', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (28, 'трыбакова', 'тр9ва', 'Рыбакова Татьяна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (29, 'есонина', 'ес7на', 'Сонина Елена', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (30, 'орумянцева', 'ор10ва', 'Румянцева Оксана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (31, 'тбабочкина', 'тб10на', 'Бабочкина Татьяна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (34, 'мбондарюк', 'мб9юк', 'Бондарюк Мария', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (36, 'игалиуллин', 'иг10ин', 'Галиуллин Ильдар', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (40, 'одоробалова', 'од11ва', 'Доробалова Оксана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (42, 'озолотова', 'оз9ва', 'Золотова Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (44, 'вкожевникова', 'вк12ва', 'Кожевникова Вероника', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (46, 'тлетникова', 'тл10ва', 'Летникова Татьяна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (51, 'мрахметов', 'мр9ов', 'Рахметов Мухидин', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (52, 'осеровская', 'ос10ая', 'Серовская Оксана', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (54, 'осоловьева', 'ос10ва', 'Соловьева Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (55, 'кстарцева', 'кс9ва', 'Старцева Ксения', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (57, 'нтаганова', 'нт9ва', 'Таганова Наталья', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (58, 'вфидоринова', 'вф11ва', 'Фидоринова Виктория', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (32, 'обармина', 'об8на', 'Бармина Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (33, 'мбондарева', 'мб10ва', 'Бондарева Марина', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (35, 'лбратова', 'лб8ва', 'Братова Лемира', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (37, 'огалиуллина', 'ог11на', 'Галиуллина Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (38, 'егжельская', 'ег10ая', 'Гжельская Евгения', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (39, 'аджелилова', 'ад10ва', 'Джелилова Алие', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (41, 'ндюканова', 'нд9ва', 'Дюканова Надежда', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (43, 'оильина', 'ои7на', 'Ильина Ольга', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (45, 'жкудайбергенова', 'жк15ва', 'Кудайбергенова Жанна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (47, 'лманкеева', 'лм9ва', 'Манкеева Лилия', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (48, 'юнаталуха', 'юн9ха', 'Наталуха Юлия', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (49, 'дниконова', 'дн9ва', 'Никонова Дарья', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (50, 'аогурцов', 'ао8ов', 'Огурцов Александр', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (53, 'лсоболева', 'лс9ва', 'Соболева Людмила', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (56, 'тсухорукова', 'тс11ва', 'Сухорукова Татьяна', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (59, 'qwer', 'asdf', 'text sequence', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (60, 'ефилипьева', 'еф10ва', 'Филипьева Елена', null, 4, null);
commit;
prompt 60 records loaded
prompt Loading HOSPITALS...
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (1, 'Алтуфьево', 1, 'Шенкурский пр-д, дом 11', '(495) 788-34-80', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (2, 'Аэропорт', 1, 'Красноармейская, дом 12', '(495) 788-34-95', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (3, 'Бабушкинская', 1, 'Менжинского, дом 23, стр. 1', '(495) 788-35-14', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (4, 'Беляево', 1, 'Профсоюзная ул., дом 104/д', '(495) 788-35-13', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (5, 'Войковская', 1, 'Ленинградское шоссе, дом 21', '(495) 788-35-19', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (6, 'Жулебино', 1, 'Авиаконструктора Миля дом 8', '(495) 788-58-30', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (7, 'Крылатское', 1, 'Осенний бульвар, дом 12, корп. 10', '(495) 788-35-27', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (8, 'Люблино', 1, 'Новороссийская, дом 28', '(495) 788-35-46', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (9, 'Маяковская', 1, 'Садовая-Триумфальная д.12/14', '(495) 984-96-18', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (10, 'Митино', 1, 'Митинская ул. д.33/1', '(495) 788-35-57', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (11, 'Октябрьское Поле', 1, 'Маршала Малиновского, дом 6, корп. 1', '(495) 788-58-05', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (12, 'Орехово', 1, 'Шипиловская д.1', '(495) 788-58-07', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (13, 'Первомайская', 1, '9-я Парковая, дом 8А', '(495) 788-58-19', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (14, 'Петровско-Разумовская', 1, 'Дмитровское шоссе, дом 30/1', '(495) 961-24-34', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (15, 'Пролетарская', 1, 'Крутицкий Вал, дом 3', '(495) 984-96-07', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (16, 'Проспект Вернадского', 1, 'Пр-т Вернадского дом 42, корп. 2', '(495) 984-96-08', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (17, 'Раменки', 1, 'Мичуринский проспект 21 к 1', '(495)984-96-20', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (18, 'Сокольники', 1, 'Русаковская, дом 22', '(495) 984-96-09', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (19, 'Строгино', 1, 'Таллинская 26', '(495)788-35-70', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (20, 'Академика Янгеля', 1, 'Варшавское шоссе, 152, корп. 2', '(495) 984-96-16', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (21, 'Ясенево', 1, 'Новоясеневский проспект дом 9', '(499) 995-51-80', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (22, 'Химки', 1, 'Московская 28/2', '(495) 984-96-25', null, 'Москва', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (23, 'Комендантский', 1, 'Комендантский пр.13 к 1 литА', '(812) 426-96-54', null, 'Питер', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (24, 'Лиговский проспект', 1, 'Марата д.50', '(812) 424-37-45', null, 'Питер', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (25, 'Удельная', 1, 'Фермское шоссе д. 32', '(812) 424-38-67', null, 'Питер', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (26, 'Родионова', 1, 'Родионова д.197', '(831) 261-35-71', null, 'НН', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (27, 'Ленинский', 1, 'Проспект Ленина 57а', '(831) 261-35-72', null, 'НН', 10, 'Все Свои');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (28, 'Алтуфьево', 2, 'Шенкурский проезд  д.11', '(495)788-35-65', null, 'Москва', 11, 'Новоклиник');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (29, 'Беляево', 2, 'Профсоюзная ул. д. 104', '(495)788-35-62', null, 'Москва', 11, 'Новоклиник');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (30, 'Пролетарская', 2, 'Крутицкий Вал, дом 3', '(495)788-35-60', null, 'Москва', 11, 'Новоклиник');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (31, 'Цветной Бульвар', 4, 'Малый сухаревский пер. д.10', '(495)788-34-78', null, 'Москва', 11, 'Институт Пластической Хирургии');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (32, 'Таганская', 5, 'Земляной вал д. 62', '(495)788-58-47', null, 'Москва', 11, 'Доктор Волос');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (33, 'Гинекология', 3, 'Земляной вал д. 62', '(495)786-23-15', null, 'Москва', 12, 'Медицинский Женский Центр');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (34, 'Сочи', 6, 'Дорога на Большой Ахун, дом 7/1', '(495)788-34-94', null, 'Сочи', 13, 'Мишлен Детокс');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (35, 'Сочи2', 6, 'Малая дорога', '(000) 111-2233', to_date('17-02-2018 00:58:44', 'dd-mm-yyyy hh24:mi:ss'), 'CITIES[4]', 13, 'Мишлен Детокс');
commit;
prompt 35 records loaded
prompt Loading SUBWAYS...
insert into SUBWAYS (id, name, city)
values (1, 'Авиамоторная', 1);
insert into SUBWAYS (id, name, city)
values (2, 'Автозаводская', 1);
insert into SUBWAYS (id, name, city)
values (3, 'Академическая', 1);
insert into SUBWAYS (id, name, city)
values (4, 'Александровский сад', 1);
insert into SUBWAYS (id, name, city)
values (5, 'Алексеевская', 1);
insert into SUBWAYS (id, name, city)
values (6, 'Алма-Атинская', 1);
insert into SUBWAYS (id, name, city)
values (7, 'Алтуфьево', 1);
insert into SUBWAYS (id, name, city)
values (8, 'Аннино', 1);
insert into SUBWAYS (id, name, city)
values (9, 'Арбатская (Арбатско-Покровская линия)', 1);
insert into SUBWAYS (id, name, city)
values (10, 'Арбатская (Филевская линия)', 1);
insert into SUBWAYS (id, name, city)
values (11, 'Аэропорт', 1);
insert into SUBWAYS (id, name, city)
values (12, 'Бабушкинская', 1);
insert into SUBWAYS (id, name, city)
values (13, 'Багратионовская', 1);
insert into SUBWAYS (id, name, city)
values (14, 'Баррикадная', 1);
insert into SUBWAYS (id, name, city)
values (15, 'Бауманская', 1);
insert into SUBWAYS (id, name, city)
values (16, 'Беговая', 1);
insert into SUBWAYS (id, name, city)
values (17, 'Белорусская', 1);
insert into SUBWAYS (id, name, city)
values (18, 'Беляево', 1);
insert into SUBWAYS (id, name, city)
values (19, 'Бибирево', 1);
insert into SUBWAYS (id, name, city)
values (20, 'Библиотека имени Ленина', 1);
insert into SUBWAYS (id, name, city)
values (21, 'Борисово', 1);
insert into SUBWAYS (id, name, city)
values (22, 'Боровицкая', 1);
insert into SUBWAYS (id, name, city)
values (23, 'Ботанический сад', 1);
insert into SUBWAYS (id, name, city)
values (24, 'Братиславская', 1);
insert into SUBWAYS (id, name, city)
values (26, 'Бульвар Дмитрия Донского', 1);
insert into SUBWAYS (id, name, city)
values (27, 'Бульвар Рокоссовского', 1);
insert into SUBWAYS (id, name, city)
values (25, 'Бульвар адмирала Ушакова', 1);
insert into SUBWAYS (id, name, city)
values (28, 'Бунинская аллея', 1);
insert into SUBWAYS (id, name, city)
values (30, 'ВДНХ', 1);
insert into SUBWAYS (id, name, city)
values (29, 'Варшавская', 1);
insert into SUBWAYS (id, name, city)
values (31, 'Владыкино', 1);
insert into SUBWAYS (id, name, city)
values (32, 'Водный стадион', 1);
insert into SUBWAYS (id, name, city)
values (33, 'Войковская', 1);
insert into SUBWAYS (id, name, city)
values (34, 'Волгоградский проспект', 1);
insert into SUBWAYS (id, name, city)
values (35, 'Волжская', 1);
insert into SUBWAYS (id, name, city)
values (36, 'Волоколамская', 1);
insert into SUBWAYS (id, name, city)
values (37, 'Воробьевы горы', 1);
insert into SUBWAYS (id, name, city)
values (38, 'Выставочная', 1);
insert into SUBWAYS (id, name, city)
values (39, 'Выхино', 1);
insert into SUBWAYS (id, name, city)
values (40, 'Деловой центр', 1);
insert into SUBWAYS (id, name, city)
values (41, 'Динамо', 1);
insert into SUBWAYS (id, name, city)
values (42, 'Дмитровская', 1);
insert into SUBWAYS (id, name, city)
values (43, 'Добрынинская', 1);
insert into SUBWAYS (id, name, city)
values (44, 'Домодедовская', 1);
insert into SUBWAYS (id, name, city)
values (45, 'Достоевская', 1);
insert into SUBWAYS (id, name, city)
values (46, 'Дубровка', 1);
insert into SUBWAYS (id, name, city)
values (47, 'Жулебино', 1);
insert into SUBWAYS (id, name, city)
values (48, 'Зябликово', 1);
insert into SUBWAYS (id, name, city)
values (49, 'Измайловская', 1);
insert into SUBWAYS (id, name, city)
values (50, 'Калужская', 1);
insert into SUBWAYS (id, name, city)
values (51, 'Кантемировская', 1);
insert into SUBWAYS (id, name, city)
values (52, 'Каховская', 1);
insert into SUBWAYS (id, name, city)
values (53, 'Каширская', 1);
insert into SUBWAYS (id, name, city)
values (54, 'Киевская', 1);
insert into SUBWAYS (id, name, city)
values (55, 'Китай-город', 1);
insert into SUBWAYS (id, name, city)
values (56, 'Кожуховская', 1);
insert into SUBWAYS (id, name, city)
values (57, 'Коломенская', 1);
insert into SUBWAYS (id, name, city)
values (58, 'Комсомольская', 1);
insert into SUBWAYS (id, name, city)
values (59, 'Коньково', 1);
insert into SUBWAYS (id, name, city)
values (60, 'Красногвардейская', 1);
insert into SUBWAYS (id, name, city)
values (61, 'Краснопресненская', 1);
insert into SUBWAYS (id, name, city)
values (62, 'Красносельская', 1);
insert into SUBWAYS (id, name, city)
values (63, 'Красные ворота', 1);
insert into SUBWAYS (id, name, city)
values (64, 'Крестьянская застава', 1);
insert into SUBWAYS (id, name, city)
values (65, 'Кропоткинская', 1);
insert into SUBWAYS (id, name, city)
values (66, 'Крылатское', 1);
insert into SUBWAYS (id, name, city)
values (67, 'Кузнецкий мост', 1);
insert into SUBWAYS (id, name, city)
values (68, 'Кузьминки', 1);
insert into SUBWAYS (id, name, city)
values (69, 'Кунцевская', 1);
insert into SUBWAYS (id, name, city)
values (70, 'Курская', 1);
insert into SUBWAYS (id, name, city)
values (71, 'Кутузовская', 1);
insert into SUBWAYS (id, name, city)
values (72, 'Ленинский проспект', 1);
insert into SUBWAYS (id, name, city)
values (73, 'Лермонтовский проспект', 1);
insert into SUBWAYS (id, name, city)
values (74, 'Лубянка', 1);
insert into SUBWAYS (id, name, city)
values (75, 'Люблино', 1);
insert into SUBWAYS (id, name, city)
values (76, 'Марксистская', 1);
insert into SUBWAYS (id, name, city)
values (77, 'Марьина роща', 1);
insert into SUBWAYS (id, name, city)
values (78, 'Марьино', 1);
insert into SUBWAYS (id, name, city)
values (79, 'Маяковская', 1);
insert into SUBWAYS (id, name, city)
values (80, 'Медведково', 1);
insert into SUBWAYS (id, name, city)
values (81, 'Международная', 1);
insert into SUBWAYS (id, name, city)
values (82, 'Менделеевская', 1);
insert into SUBWAYS (id, name, city)
values (83, 'Митино', 1);
insert into SUBWAYS (id, name, city)
values (84, 'Молодежная', 1);
insert into SUBWAYS (id, name, city)
values (85, 'Мякинино', 1);
insert into SUBWAYS (id, name, city)
values (86, 'Нагатинская', 1);
insert into SUBWAYS (id, name, city)
values (87, 'Нагорная', 1);
insert into SUBWAYS (id, name, city)
values (88, 'Нахимовский проспект', 1);
insert into SUBWAYS (id, name, city)
values (89, 'Новогиреево', 1);
insert into SUBWAYS (id, name, city)
values (90, 'Новокосино', 1);
insert into SUBWAYS (id, name, city)
values (91, 'Новокузнецкая', 1);
insert into SUBWAYS (id, name, city)
values (92, 'Новослободская', 1);
insert into SUBWAYS (id, name, city)
values (93, 'Новоясеневская', 1);
insert into SUBWAYS (id, name, city)
values (94, 'Новые Черемушки', 1);
insert into SUBWAYS (id, name, city)
values (95, 'Октябрьская', 1);
insert into SUBWAYS (id, name, city)
values (96, 'Октябрьское поле', 1);
insert into SUBWAYS (id, name, city)
values (97, 'Орехово', 1);
insert into SUBWAYS (id, name, city)
values (98, 'Отрадное', 1);
insert into SUBWAYS (id, name, city)
values (99, 'Охотный ряд', 1);
insert into SUBWAYS (id, name, city)
values (100, 'Павелецкая', 1);
commit;
prompt 100 records committed...
insert into SUBWAYS (id, name, city)
values (102, 'Парк Победы', 1);
insert into SUBWAYS (id, name, city)
values (101, 'Парк культуры', 1);
insert into SUBWAYS (id, name, city)
values (103, 'Партизанская', 1);
insert into SUBWAYS (id, name, city)
values (104, 'Первомайская', 1);
insert into SUBWAYS (id, name, city)
values (105, 'Перово', 1);
insert into SUBWAYS (id, name, city)
values (106, 'Петровско-Разумовская', 1);
insert into SUBWAYS (id, name, city)
values (107, 'Печатники', 1);
insert into SUBWAYS (id, name, city)
values (108, 'Пионерская', 1);
insert into SUBWAYS (id, name, city)
values (109, 'Планерная', 1);
insert into SUBWAYS (id, name, city)
values (110, 'Площадь Ильича', 1);
insert into SUBWAYS (id, name, city)
values (111, 'Площадь Революции', 1);
insert into SUBWAYS (id, name, city)
values (112, 'Полежаевская', 1);
insert into SUBWAYS (id, name, city)
values (113, 'Полянка', 1);
insert into SUBWAYS (id, name, city)
values (114, 'Пражская', 1);
insert into SUBWAYS (id, name, city)
values (115, 'Преображенская площадь', 1);
insert into SUBWAYS (id, name, city)
values (116, 'Пролетарская', 1);
insert into SUBWAYS (id, name, city)
values (117, 'Проспект Вернадского', 1);
insert into SUBWAYS (id, name, city)
values (118, 'Проспект Мира', 1);
insert into SUBWAYS (id, name, city)
values (119, 'Профсоюзная', 1);
insert into SUBWAYS (id, name, city)
values (120, 'Пушкинская', 1);
insert into SUBWAYS (id, name, city)
values (121, 'Пятницкое шоссе', 1);
insert into SUBWAYS (id, name, city)
values (122, 'Речной вокзал', 1);
insert into SUBWAYS (id, name, city)
values (123, 'Рижская', 1);
insert into SUBWAYS (id, name, city)
values (124, 'Римская', 1);
insert into SUBWAYS (id, name, city)
values (125, 'Рязанский проспект', 1);
insert into SUBWAYS (id, name, city)
values (126, 'Савеловская', 1);
insert into SUBWAYS (id, name, city)
values (127, 'Свиблово', 1);
insert into SUBWAYS (id, name, city)
values (128, 'Севастопольская', 1);
insert into SUBWAYS (id, name, city)
values (129, 'Семеновская', 1);
insert into SUBWAYS (id, name, city)
values (130, 'Серпуховская', 1);
insert into SUBWAYS (id, name, city)
values (131, 'Славянский бульвар', 1);
insert into SUBWAYS (id, name, city)
values (132, 'Смоленская (Арбатско-Покровская линия)', 1);
insert into SUBWAYS (id, name, city)
values (133, 'Смоленская (Филевская линия)', 1);
insert into SUBWAYS (id, name, city)
values (134, 'Сокол', 1);
insert into SUBWAYS (id, name, city)
values (135, 'Сокольники', 1);
insert into SUBWAYS (id, name, city)
values (136, 'Спартак', 1);
insert into SUBWAYS (id, name, city)
values (137, 'Спортивная', 1);
insert into SUBWAYS (id, name, city)
values (138, 'Сретенский бульвар', 1);
insert into SUBWAYS (id, name, city)
values (139, 'Строгино', 1);
insert into SUBWAYS (id, name, city)
values (140, 'Студенческая', 1);
insert into SUBWAYS (id, name, city)
values (141, 'Сухаревская', 1);
insert into SUBWAYS (id, name, city)
values (142, 'Сходненская', 1);
insert into SUBWAYS (id, name, city)
values (143, 'Таганская', 1);
insert into SUBWAYS (id, name, city)
values (144, 'Тверская', 1);
insert into SUBWAYS (id, name, city)
values (145, 'Театральная', 1);
insert into SUBWAYS (id, name, city)
values (146, 'Текстильщики', 1);
insert into SUBWAYS (id, name, city)
values (147, 'Теплый стан', 1);
insert into SUBWAYS (id, name, city)
values (148, 'Тимирязевская', 1);
insert into SUBWAYS (id, name, city)
values (149, 'Третьяковская', 1);
insert into SUBWAYS (id, name, city)
values (150, 'Тропарево', 1);
insert into SUBWAYS (id, name, city)
values (151, 'Трубная', 1);
insert into SUBWAYS (id, name, city)
values (152, 'Тульская', 1);
insert into SUBWAYS (id, name, city)
values (153, 'Тургеневская', 1);
insert into SUBWAYS (id, name, city)
values (154, 'Тушинская', 1);
insert into SUBWAYS (id, name, city)
values (159, 'Улица 1905 года', 1);
insert into SUBWAYS (id, name, city)
values (155, 'Улица Академика Янгеля', 1);
insert into SUBWAYS (id, name, city)
values (156, 'Улица Горчакова', 1);
insert into SUBWAYS (id, name, city)
values (157, 'Улица Скобелевская', 1);
insert into SUBWAYS (id, name, city)
values (158, 'Улица Старокачаловская', 1);
insert into SUBWAYS (id, name, city)
values (160, 'Университет', 1);
insert into SUBWAYS (id, name, city)
values (161, 'Филевский парк', 1);
insert into SUBWAYS (id, name, city)
values (162, 'Фили', 1);
insert into SUBWAYS (id, name, city)
values (163, 'Фрунзенская', 1);
insert into SUBWAYS (id, name, city)
values (164, 'Царицыно', 1);
insert into SUBWAYS (id, name, city)
values (165, 'Цветной бульвар', 1);
insert into SUBWAYS (id, name, city)
values (166, 'Черкизовская', 1);
insert into SUBWAYS (id, name, city)
values (167, 'Чертановская', 1);
insert into SUBWAYS (id, name, city)
values (168, 'Чеховская', 1);
insert into SUBWAYS (id, name, city)
values (169, 'Чистые пруды', 1);
insert into SUBWAYS (id, name, city)
values (170, 'Чкаловская', 1);
insert into SUBWAYS (id, name, city)
values (171, 'Шаболовская', 1);
insert into SUBWAYS (id, name, city)
values (172, 'Шипиловская', 1);
insert into SUBWAYS (id, name, city)
values (173, 'Шоссе Энтузиастов', 1);
insert into SUBWAYS (id, name, city)
values (174, 'Щелковская', 1);
insert into SUBWAYS (id, name, city)
values (175, 'Щукинская', 1);
insert into SUBWAYS (id, name, city)
values (176, 'Электрозаводская', 1);
insert into SUBWAYS (id, name, city)
values (177, 'Юго-Западная', 1);
insert into SUBWAYS (id, name, city)
values (178, 'Южная', 1);
insert into SUBWAYS (id, name, city)
values (179, 'Ясенево', 1);
commit;
prompt 179 records loaded
prompt Loading USER_DEP_ALLOC...
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (1, 1, null, 2);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (2, 5, null, 5);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (1, 2, null, 3);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (8, 6, null, 12);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (5, 7, null, 8);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (9, 8, null, 13);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (10, 7, null, 14);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (11, 6, null, 16);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (11, 8, null, 17);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (58, 7, null, 97);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (14, 6, null, 21);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (14, 8, null, 20);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (19, 6, null, 36);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (19, 9, null, 35);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (27, 6, null, 60);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (27, 8, null, 59);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (26, 6, null, 56);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (26, 9, null, 57);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (30, 8, null, 67);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (28, 6, null, 63);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (28, 9, null, 62);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (28, 8, null, 61);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (29, 6, null, 65);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (29, 9, null, 66);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (29, 8, null, 64);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (15, 6, null, 22);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (15, 8, null, 23);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (60, 7, null, 99);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (2, 3, null, 4);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (4, 9, null, 7);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (30, 6, null, 69);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (30, 9, null, 68);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (23, 6, null, 48);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (23, 9, null, 46);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (23, 8, null, 47);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (15, 9, null, 24);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (1, -1, null, 1);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (11, 9, null, 15);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (12, 10, null, 18);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (6, 6, null, 9);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (31, 7, null, 70);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (32, 7, null, 71);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (36, 7, null, 75);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (37, 7, null, 76);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (38, 7, null, 77);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (42, 7, null, 81);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (45, 7, null, 84);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (48, 7, null, 87);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (49, 7, null, 88);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (50, 7, null, 89);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (51, 7, null, 90);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (54, 7, null, 93);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (57, 7, null, 96);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (59, 5, to_date('27-02-2018 14:19:45', 'dd-mm-yyyy hh24:mi:ss'), 98);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (3, 2, null, 6);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (7, 10, null, 11);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (6, 9, null, 10);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (33, 7, null, 72);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (34, 7, null, 73);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (35, 7, null, 74);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (39, 7, null, 78);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (40, 7, null, 79);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (41, 7, null, 80);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (43, 7, null, 82);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (44, 7, null, 83);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (46, 7, null, 85);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (47, 7, null, 86);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (52, 7, null, 91);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (53, 7, null, 92);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (55, 7, null, 94);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (56, 7, null, 95);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (14, 9, null, 19);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (22, 6, null, 43);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (22, 9, null, 44);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (22, 8, null, 45);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (18, 6, null, 31);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (18, 9, null, 32);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (18, 8, null, 33);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (19, 8, null, 34);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (17, 6, null, 30);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (17, 9, null, 28);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (17, 8, null, 29);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (24, 6, null, 50);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (24, 9, null, 51);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (24, 8, null, 49);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (27, 9, null, 58);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (21, 6, null, 42);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (21, 9, null, 41);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (21, 8, null, 40);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (26, 8, null, 55);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (20, 6, null, 37);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (20, 9, null, 38);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (20, 8, null, 39);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (16, 6, null, 25);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (16, 9, null, 26);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (16, 8, null, 27);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (25, 6, null, 54);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (25, 9, null, 53);
insert into USER_DEP_ALLOC (user_id, dep_id, deleted, id)
values (25, 8, null, 52);
commit;
prompt 99 records loaded
prompt Enabling foreign key constraints for ACCESS_DEP...
alter table ACCESS_DEP enable constraint FK_DEP_ACC1;
alter table ACCESS_DEP enable constraint FK_DEP_ACC2;
alter table ACCESS_DEP enable constraint FK_DEP_ACC3;
alter table ACCESS_DEP enable constraint FK_DEP_ACC4;
alter table ACCESS_DEP enable constraint FK_DEP_ACC5;
prompt Enabling foreign key constraints for SOURCE_MAN_DETAIL...
alter table SOURCE_MAN_DETAIL enable constraint FK_SOURCE_MAN_DET;
prompt Enabling foreign key constraints for USERS...
alter table USERS enable constraint FK_USERS;
prompt Enabling foreign key constraints for CALL_BASE...
alter table CALL_BASE enable constraint FK_CALL_BASE1;
alter table CALL_BASE enable constraint FK_CALL_BASE2;
alter table CALL_BASE enable constraint FK_CALL_BASE3;
alter table CALL_BASE enable constraint FK_CALL_BASE4;
alter table CALL_BASE enable constraint FK_CALL_BASE6;
alter table CALL_BASE enable constraint FK_CALL_BASE7;
alter table CALL_BASE enable constraint FK_CALL_BASE8;
prompt Enabling foreign key constraints for HOSPITALS...
alter table HOSPITALS enable constraint FK_HOSPITALS;
alter table HOSPITALS enable constraint FK_HOSPITALS1;
prompt Enabling foreign key constraints for CALL_BASE_CLINIC...
alter table CALL_BASE_CLINIC enable constraint FK_CALL_BASE_CLINIC;
alter table CALL_BASE_CLINIC enable constraint FK_CALL_BASE_CLINIC1;
prompt Enabling foreign key constraints for CALL_BASE_HIST...
alter table CALL_BASE_HIST enable constraint FK_CALL_BASE_HIST;
alter table CALL_BASE_HIST enable constraint FK_CALL_BASE_HIST1;
alter table CALL_BASE_HIST enable constraint FK_CALL_BASE_HIST2;
prompt Enabling foreign key constraints for USER_DEP_ALLOC...
alter table USER_DEP_ALLOC enable constraint FK_USER_DEP_1;
alter table USER_DEP_ALLOC enable constraint FK_USER_DEP_2;
prompt Enabling triggers for CALL_TYPE...
alter table CALL_TYPE enable all triggers;
prompt Enabling triggers for DEPARTAMENTS...
alter table DEPARTAMENTS enable all triggers;
prompt Enabling triggers for SERVICES...
alter table SERVICES enable all triggers;
prompt Enabling triggers for SOURCE_AUTO...
alter table SOURCE_AUTO enable all triggers;
prompt Enabling triggers for SOURCE_MAN...
alter table SOURCE_MAN enable all triggers;
prompt Enabling triggers for ACCESS_DEP...
alter table ACCESS_DEP enable all triggers;
prompt Enabling triggers for CALL_THEME...
alter table CALL_THEME enable all triggers;
prompt Enabling triggers for MED_STATUS...
alter table MED_STATUS enable all triggers;
prompt Enabling triggers for SOURCE_MAN_DETAIL...
alter table SOURCE_MAN_DETAIL enable all triggers;
prompt Enabling triggers for ROLES...
alter table ROLES enable all triggers;
prompt Enabling triggers for USERS...
alter table USERS enable all triggers;
prompt Enabling triggers for CALL_BASE...
alter table CALL_BASE enable all triggers;
prompt Enabling triggers for HOSPITALS...
alter table HOSPITALS enable all triggers;
prompt Enabling triggers for CALL_BASE_CLINIC...
alter table CALL_BASE_CLINIC enable all triggers;
prompt Enabling triggers for CALL_BASE_HIST...
alter table CALL_BASE_HIST enable all triggers;
prompt Enabling triggers for SUBWAYS...
alter table SUBWAYS enable all triggers;
prompt Enabling triggers for USER_DEP_ALLOC...
alter table USER_DEP_ALLOC enable all triggers;
set feedback on
set define on
prompt Done.
