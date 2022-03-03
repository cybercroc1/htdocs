prompt PL/SQL Developer import file
prompt Created on 4 ���� 2018 �. by Dexp
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
values (1, '���������');
insert into CALL_TYPE (id, name)
values (2, '���������');
commit;
prompt 3 records loaded
prompt Loading DEPARTAMENTS...
insert into DEPARTAMENTS (id, name, deleted)
values (-1, 'all', null);
insert into DEPARTAMENTS (id, name, deleted)
values (1, '�������������', null);
insert into DEPARTAMENTS (id, name, deleted)
values (2, '�����������', to_date('21-02-2018 11:23:15', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (3, '������������', to_date('21-02-2018 11:23:07', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (4, 'fgdfgf', to_date('24-01-2018 00:59:17', 'dd-mm-yyyy hh24:mi:ss'));
insert into DEPARTAMENTS (id, name, deleted)
values (5, '����-�����', null);
insert into DEPARTAMENTS (id, name, deleted)
values (6, '�����������', null);
insert into DEPARTAMENTS (id, name, deleted)
values (7, '�����������', null);
insert into DEPARTAMENTS (id, name, deleted)
values (8, '���������', null);
insert into DEPARTAMENTS (id, name, deleted)
values (9, '��������', null);
insert into DEPARTAMENTS (id, name, deleted)
values (10, '����������', null);
commit;
prompt 11 records loaded
prompt Loading SERVICES...
insert into SERVICES (id, name, deleted)
values (-1, 'all', null);
insert into SERVICES (id, name, deleted)
values (1, '������������', null);
insert into SERVICES (id, name, deleted)
values (2, '������������', null);
insert into SERVICES (id, name, deleted)
values (3, '�����������', null);
insert into SERVICES (id, name, deleted)
values (4, '��������', null);
insert into SERVICES (id, name, deleted)
values (5, '����������', null);
insert into SERVICES (id, name, deleted)
values (6, '������', null);
commit;
prompt 7 records loaded
prompt Loading SOURCE_AUTO...
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (-1, 'all', 'all', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (112, '5555555555', '�������� (����) ����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (106, '5555555557', '�������� �������� ������� ��� �������� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (6, '2290101', '��� - ������ - ����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (7, '2290126', '��� - ������ - ����-������� ������������+����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (8, '2290128', '��� - ���� - www.slimclinic.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (9, '2290136', '��� - ������ - www.contact-center.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (10, '2290145', '��� - ������ - www.vse-svoi.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (11, '4959671943', '��� - ������ - ���� ����� ���� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (12, '4959671946', '��� - ���� - www.novoclinic-msk.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (13, '4959671948', '��� - ���� - www.novoklinik.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (14, '4959849601', '��� - ������ ��������-1 - ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (15, '4959849603', '��� - ����� - ����� ����� ������������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (16, '4959849604', '��� - ������ - �������� ����������� � Google Adwords ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (17, '4959849606', '��� - ������ - ��������� ��� ������� ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (18, '4959849610', '��� - ������ - �������-����� ����������� � Google Adwords ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (19, '4959849612', '��� - ������ - Wilstream �������� ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (20, '4959849614', '��� - ������ - ���� �������� �������� (����� � ���)', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (21, '4959849617', '��� - �������� - ����������-��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (22, '4959849623', '��� - ������ - �������� �������� ����: molodeu context plastica', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (23, '4959849624', '��� - ������ - �������� �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (24, '4959849628', '��� - ������ - ��� Google Adwords �� �����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (25, '4959849631', '��� - ������ - ���� ������������ ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (26, '4992722761', '��� - ������ �������� - https://vse-svoi.ru/nn/', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (27, '4992722767', '��� - ������ - �������� ���� ������������ �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (28, '4992722772', '��� - ���� �� (����� � �� �������) - http://www.7894444.ru/?ref_city=nn', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (29, '4993700262', '��� - ������ - �������� ���� ������������ ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (30, '4993700268', '��� - ������ - ���� ����� ���-�������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (31, '4993700279', '��� - ������ - ����������� ����� ����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (32, '4993700297', '��� - ������ - �������������� ����������, ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (33, '4993700299', '��� - ������ - ���� ��� ���� ������ mammoplastik.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (34, '4999511741', '��� - �����  - https://vse-svoi.ru/spb/', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (35, '4999511742', '��� - ������ - ������� �������� +����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (36, '4999511743', '��� - ���� ����� - http://www.7894444.ru/?ref_city=spb', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (37, '4999511747', '��� - ����� �������� - ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (38, '4999511749', '��� - ������ - ��� ��������� ������������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (39, '4999955182', '��� - ������ - ���� ����� ���� ������������ ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (40, '4999955183', '��� - ������ - ���� ����� ���� ������������ �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (41, '74950110561', '��� - ������ - Digital G �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (42, '74950110562', '��� - ������ - Digital G ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (43, '74950110568', '��� - ������ - ���� ���������������� �.���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (44, '74950110571', '��� - ������ - IT Planet ���� ������������ ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (45, '74950110572', '��� - ������ - IT Plane ���� ������������ �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (46, '74950110574', '��� - ������ - IT Plane ���� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (47, '74950110581', '��� - ������ - ���� ����� ������ �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (48, '74950110586', '��� - ������ - ���� 7894444 vse-svoi.moscow', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (49, '74950110589', '���  - ������ - ������������ ���� ��������� ��� -1', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (50, '74950110591', '��� - ������ - ������ ������� ����������� ���� ���� �', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (51, '74950110593', '��� - ������ - ������ ������� ����������� ���� ���� �', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (52, '74950110596', '��� - ������ - ������ ������� ����������� ���� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (53, '74950110597', '���  - ������ - ���� ����� ������ ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (54, '7850303', '��� - ������ - ������������ ���� �����, ���������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (55, '7862120', '��� - ������ - ���� �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (56, '7883478', '��� - ������ - ����� ���� ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (57, '7883482', '���  - ����� - ������������ ����� ���� ��������� ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (58, '7883485', '��� - ������ - 2Gis ������������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (59, '7883494', '��� - c��� - www.mishilen-detox.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (60, '7883508', '��� - ������ �������� ��������  - ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (61, '7883510', '��� - ������ - ���������������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (62, '7883524', '����� - ������ - ����� www.novoclinic.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (63, '7883525', '��� - ������ - ������-��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (64, '7883529', '��� - ������ - ��������������� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (65, '7883532', '��� - ������ - ������� ���� �', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (66, '7883540', '��� - ���� - www.sochidetox.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (67, '7883543', '��� - ������ - ZOON ����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (68, '7883552', '��� - ������ - ������� ���� �', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (69, '7883554', '����� - ������ - ����� www.wilstream.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (70, '7883558', '��� - ������ - www.wilstream-msk.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (71, '7883559', '��� - �������� - ����������-��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (72, '7883560', '��� - ������ - ����� ���� ���������� ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (73, '7883565', '��� - ������ - ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (74, '7883571', '��� - ������ - 2Gis ����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (75, '7883586', '��� - ������ - ��� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (76, '7883590', '��� - ������ - ��� �������� ����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (77, '7883591', '��� - �������� - ������ ����� ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (78, '7883593', '����� - ������ - ����� www.doktorvolos.ru ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (79, '7883595', '��� - ������ - ������� ��� �����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (80, '7883597', '��� - ������ - ������� ��� �����������-2', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (81, '7883598', '��� - ������ - ZOON ������������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (82, '7884982', '��� - ���� �� (����� � �� �������) - http://www.7894444.ru/?ref_city=nn', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (83, '7884984', '��� - ���� - www.hochudetey.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (84, '7884986', '��� - ������ - ����������� �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (85, '7884988', '��� - ������ - ��������� ���', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (86, '7884989', '��� - ���� - www.doktorvolos-msk.ru ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (87, '7885801', '��� - ���� - www.molodeu.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (88, '7885802', '��� - ������ - ��������� ����� ��� ���� ������ ', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (89, '7885803', '��� - ������ - ��������� ����� (�.�������) ���������� ������� @novoklinik.estetika', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (90, '7885806', '��� - ���� - www.institutbeauty.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (91, '7885808', '��� - ������ �������� - ��������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (92, '7885810', '��� - ������ - ��� �������� �������� ����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (93, '7885814', '��� - c��� - www.vsegdakrasiva.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (94, '7885819', '��� - ��������� 18.03.15  - ����� ���� ��� ����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (95, '7885833', '��� - ��� - ������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (96, '7885837', '��� - ������ - ���� ��� � ����� ����������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (97, '7885840', '��� - ���� - www.medzhencentre.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (98, '7885845', '��� - ���� ������ - www.7894444.ru www.elenshleger.ru www.terezanov.ru', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (99, '7885847', '��� - ������ - ����� ���� ������ �����', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (100, '7885848', '��� - ������ - "����� �������" ��', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (101, '8312613335', '��� - �� - ������� ������ �� �� �������', null);
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (108, '74950110621', '���� 74950110621', to_date('27-02-2018 14:31:54', 'dd-mm-yyyy hh24:mi:ss'));
commit;
prompt 100 records committed...
insert into SOURCE_AUTO (id, bnumber, name, deleted)
values (111, '74950110621', '��� - ���-������-������', null);
commit;
prompt 101 records loaded
prompt Loading SOURCE_MAN...
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (-1, 'all', null, 'all', '0');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (1, '�������� � �������� ����', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (2, '����-�������', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (3, '��������', null, '��� ����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (4, '���������', null, '���������', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (5, '�������� � �����', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (6, '�������� ��� �������/����� ����', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (7, '���������� � ��������/�����', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (8, '���������', null, '������� �����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (9, '�����', null, '�����', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (10, '�� �������', null, '�� �������', '1');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (11, '���������� ����������', null, '����������', '1');
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
values (21, 'SMS ��������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (22, '������ � �������� �����', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (23, '������ � ������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (24, '���� �����', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (25, '���������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (26, '�������� � ������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (27, '�������� � �����', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (28, '���������� �����', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (29, '�����', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (30, '����� ������� � �������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (31, '������� � ���������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (32, '��������� ���', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (33, '������������', null, null, '2');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (34, 'Reserved9', to_date('11-02-2018 16:18:10', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (35, 'Reserved10', to_date('11-02-2018 16:17:54', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (36, 'Reserved11', to_date('11-02-2018 16:18:00', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (37, 'Reserved12', to_date('11-02-2018 16:18:05', 'dd-mm-yyyy hh24:mi:ss'), null, '3');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (38, '������', null, null, '99');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (39, '�� ������', null, null, '99');
insert into SOURCE_MAN (id, name, deleted, detail, priority)
values (40, '�����', to_date('13-02-2018', 'dd-mm-yyyy'), null, '100');
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
values (1, '����������� ������', 1, null);
insert into CALL_THEME (id, name, target, deleted)
values (2, '���������� ��� �����������', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (3, '������������� � �������', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (4, '�������', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (5, '��������������� ���������', 0, null);
insert into CALL_THEME (id, name, target, deleted)
values (6, '������� �� ������', 0, null);
commit;
prompt 6 records loaded
prompt Loading MED_STATUS...
insert into MED_STATUS (id, name, color)
values (1, '�����', 'black');
insert into MED_STATUS (id, name, color)
values (2, '���������', 'green');
insert into MED_STATUS (id, name, color)
values (3, '�����������', 'blue');
insert into MED_STATUS (id, name, color)
values (4, '��������', 'orange');
insert into MED_STATUS (id, name, color)
values (5, '������ ��������', 'orangered');
insert into MED_STATUS (id, name, color)
values (6, '������ � �������', '#e626cd');
insert into MED_STATUS (id, name, color)
values (7, '�����/�������', 'magenta');
insert into MED_STATUS (id, name, color)
values (8, '������', 'red');
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
values (506, 4, '��������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (509, 4, '���', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (520, 9, '������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (518, 9, '����-�����', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (519, 9, '����-�����', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (526, 10, '������������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (527, 10, '��������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (525, 10, '������������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (529, 10, '������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (528, 10, '���������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (523, 10, '����', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (522, 10, '�������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (521, 10, '�����������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (530, 10, '�����-��������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (524, 10, '�������', null);
insert into SOURCE_MAN_DETAIL (id, source_man_id, name, deleted)
values (531, 29, '���������� �����', null);
commit;
prompt 20 records loaded
prompt Loading ROLES...
insert into ROLES (id, name)
values (1, '�������������');
insert into ROLES (id, name)
values (2, '�����������');
insert into ROLES (id, name)
values (3, '������������');
insert into ROLES (id, name)
values (4, '��������');
commit;
prompt 4 records loaded
prompt Loading USERS...
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (1, 'admin', 'admin', 'admin', null, 1, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (2, 'loginin', 'passwsw', '������������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (3, 'nablu', 'bluna', '������� ������������', null, 3, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (4, 'super', 'visor', '����������', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (5, 'stom', 'stom', '����������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (6, 'plast', 'plast', '�������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (7, 'gin', 'gin', '���������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (8, 'kosm', 'kosm', '����������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (9, 'trix', 'trix', '��������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (10, '���������', '��9��', '�������� ����', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (11, '����������', '��10��', '��������� ������', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (12, 'Zhanna', 'Alibekova', '��������� �����', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (13, 'Olga', 'Lukiamchuk', '��������� �����', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (14, '��������', '��8��', '������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (15, '�������', '��7��', '������ ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (16, '������', '��6��', '����� ��������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (17, '����', '��4��', '��� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (18, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (19, '��������', '��8��', '������� ������', null, 4, to_date('02-03-2018 09:55:09', 'dd-mm-yyyy hh24:mi:ss'));
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (20, '������������', '��12��', '����������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (21, '���������', '��9��', '�������� ��������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (22, '�������', '��7��', '������ ���������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (23, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (24, '��������', '��8��', '������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (25, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (26, '�����������', '��11��', '���������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (27, '����������', '��10��', '��������� ����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (28, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (29, '�������', '��7��', '������ �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (30, '����������', '��10��', '��������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (31, '����������', '��10��', '��������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (34, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (36, '����������', '��10��', '��������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (40, '�����������', '��11��', '���������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (42, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (44, '������������', '��12��', '����������� ��������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (46, '����������', '��10��', '��������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (51, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (52, '����������', '��10��', '��������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (54, '����������', '��10��', '��������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (55, '���������', '��9��', '�������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (57, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (58, '�����������', '��11��', '���������� ��������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (32, '��������', '��8��', '������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (33, '����������', '��10��', '��������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (35, '��������', '��8��', '������� ������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (37, '�����������', '��11��', '���������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (38, '����������', '��10��', '��������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (39, '����������', '��10��', '��������� ����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (41, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (43, '�������', '��7��', '������ �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (45, '���������������', '��15��', '�������������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (47, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (48, '���������', '��9��', '�������� ����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (49, '���������', '��9��', '�������� �����', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (50, '��������', '��8��', '������� ���������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (53, '���������', '��9��', '�������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (56, '�����������', '��11��', '���������� �������', null, 4, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (59, 'qwer', 'asdf', 'text sequence', null, 2, null);
insert into USERS (id, login, password, fio, deleted, role_id, activity)
values (60, '����������', '��10��', '��������� �����', null, 4, null);
commit;
prompt 60 records loaded
prompt Loading HOSPITALS...
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (1, '���������', 1, '���������� ��-�, ��� 11', '(495) 788-34-80', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (2, '��������', 1, '���������������, ��� 12', '(495) 788-34-95', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (3, '������������', 1, '�����������, ��� 23, ���. 1', '(495) 788-35-14', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (4, '�������', 1, '����������� ��., ��� 104/�', '(495) 788-35-13', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (5, '����������', 1, '������������� �����, ��� 21', '(495) 788-35-19', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (6, '��������', 1, '���������������� ���� ��� 8', '(495) 788-58-30', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (7, '����������', 1, '������� �������, ��� 12, ����. 10', '(495) 788-35-27', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (8, '�������', 1, '��������������, ��� 28', '(495) 788-35-46', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (9, '����������', 1, '�������-������������ �.12/14', '(495) 984-96-18', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (10, '������', 1, '��������� ��. �.33/1', '(495) 788-35-57', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (11, '����������� ����', 1, '������� ������������, ��� 6, ����. 1', '(495) 788-58-05', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (12, '�������', 1, '����������� �.1', '(495) 788-58-07', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (13, '������������', 1, '9-� ��������, ��� 8�', '(495) 788-58-19', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (14, '���������-�����������', 1, '����������� �����, ��� 30/1', '(495) 961-24-34', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (15, '������������', 1, '��������� ���, ��� 3', '(495) 984-96-07', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (16, '�������� �����������', 1, '��-� ����������� ��� 42, ����. 2', '(495) 984-96-08', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (17, '�������', 1, '����������� �������� 21 � 1', '(495)984-96-20', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (18, '����������', 1, '�����������, ��� 22', '(495) 984-96-09', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (19, '��������', 1, '���������� 26', '(495)788-35-70', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (20, '��������� ������', 1, '���������� �����, 152, ����. 2', '(495) 984-96-16', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (21, '�������', 1, '�������������� �������� ��� 9', '(499) 995-51-80', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (22, '�����', 1, '���������� 28/2', '(495) 984-96-25', null, '������', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (23, '�������������', 1, '������������� ��.13 � 1 ����', '(812) 426-96-54', null, '�����', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (24, '��������� ��������', 1, '������ �.50', '(812) 424-37-45', null, '�����', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (25, '��������', 1, '�������� ����� �. 32', '(812) 424-38-67', null, '�����', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (26, '���������', 1, '��������� �.197', '(831) 261-35-71', null, '��', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (27, '���������', 1, '�������� ������ 57�', '(831) 261-35-72', null, '��', 10, '��� ����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (28, '���������', 2, '���������� ������  �.11', '(495)788-35-65', null, '������', 11, '����������');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (29, '�������', 2, '����������� ��. �. 104', '(495)788-35-62', null, '������', 11, '����������');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (30, '������������', 2, '��������� ���, ��� 3', '(495)788-35-60', null, '������', 11, '����������');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (31, '������� �������', 4, '����� ����������� ���. �.10', '(495)788-34-78', null, '������', 11, '�������� ������������ ��������');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (32, '���������', 5, '�������� ��� �. 62', '(495)788-58-47', null, '������', 11, '������ �����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (33, '�����������', 3, '�������� ��� �. 62', '(495)786-23-15', null, '������', 12, '����������� ������� �����');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (34, '����', 6, '������ �� ������� ����, ��� 7/1', '(495)788-34-94', null, '����', 13, '������ ������');
insert into HOSPITALS (id, name, service_id, address, phone, deleted, city, manager_id, trademark)
values (35, '����2', 6, '����� ������', '(000) 111-2233', to_date('17-02-2018 00:58:44', 'dd-mm-yyyy hh24:mi:ss'), 'CITIES[4]', 13, '������ ������');
commit;
prompt 35 records loaded
prompt Loading SUBWAYS...
insert into SUBWAYS (id, name, city)
values (1, '������������', 1);
insert into SUBWAYS (id, name, city)
values (2, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (3, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (4, '��������������� ���', 1);
insert into SUBWAYS (id, name, city)
values (5, '������������', 1);
insert into SUBWAYS (id, name, city)
values (6, '����-��������', 1);
insert into SUBWAYS (id, name, city)
values (7, '���������', 1);
insert into SUBWAYS (id, name, city)
values (8, '������', 1);
insert into SUBWAYS (id, name, city)
values (9, '��������� (��������-���������� �����)', 1);
insert into SUBWAYS (id, name, city)
values (10, '��������� (��������� �����)', 1);
insert into SUBWAYS (id, name, city)
values (11, '��������', 1);
insert into SUBWAYS (id, name, city)
values (12, '������������', 1);
insert into SUBWAYS (id, name, city)
values (13, '���������������', 1);
insert into SUBWAYS (id, name, city)
values (14, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (15, '����������', 1);
insert into SUBWAYS (id, name, city)
values (16, '�������', 1);
insert into SUBWAYS (id, name, city)
values (17, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (18, '�������', 1);
insert into SUBWAYS (id, name, city)
values (19, '��������', 1);
insert into SUBWAYS (id, name, city)
values (20, '���������� ����� ������', 1);
insert into SUBWAYS (id, name, city)
values (21, '��������', 1);
insert into SUBWAYS (id, name, city)
values (22, '����������', 1);
insert into SUBWAYS (id, name, city)
values (23, '������������ ���', 1);
insert into SUBWAYS (id, name, city)
values (24, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (26, '������� ������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (27, '������� �������������', 1);
insert into SUBWAYS (id, name, city)
values (25, '������� �������� �������', 1);
insert into SUBWAYS (id, name, city)
values (28, '��������� �����', 1);
insert into SUBWAYS (id, name, city)
values (30, '����', 1);
insert into SUBWAYS (id, name, city)
values (29, '����������', 1);
insert into SUBWAYS (id, name, city)
values (31, '���������', 1);
insert into SUBWAYS (id, name, city)
values (32, '������ �������', 1);
insert into SUBWAYS (id, name, city)
values (33, '����������', 1);
insert into SUBWAYS (id, name, city)
values (34, '������������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (35, '��������', 1);
insert into SUBWAYS (id, name, city)
values (36, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (37, '��������� ����', 1);
insert into SUBWAYS (id, name, city)
values (38, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (39, '������', 1);
insert into SUBWAYS (id, name, city)
values (40, '������� �����', 1);
insert into SUBWAYS (id, name, city)
values (41, '������', 1);
insert into SUBWAYS (id, name, city)
values (42, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (43, '������������', 1);
insert into SUBWAYS (id, name, city)
values (44, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (45, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (46, '��������', 1);
insert into SUBWAYS (id, name, city)
values (47, '��������', 1);
insert into SUBWAYS (id, name, city)
values (48, '���������', 1);
insert into SUBWAYS (id, name, city)
values (49, '������������', 1);
insert into SUBWAYS (id, name, city)
values (50, '���������', 1);
insert into SUBWAYS (id, name, city)
values (51, '��������������', 1);
insert into SUBWAYS (id, name, city)
values (52, '���������', 1);
insert into SUBWAYS (id, name, city)
values (53, '���������', 1);
insert into SUBWAYS (id, name, city)
values (54, '��������', 1);
insert into SUBWAYS (id, name, city)
values (55, '�����-�����', 1);
insert into SUBWAYS (id, name, city)
values (56, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (57, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (58, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (59, '��������', 1);
insert into SUBWAYS (id, name, city)
values (60, '�����������������', 1);
insert into SUBWAYS (id, name, city)
values (61, '�����������������', 1);
insert into SUBWAYS (id, name, city)
values (62, '��������������', 1);
insert into SUBWAYS (id, name, city)
values (63, '������� ������', 1);
insert into SUBWAYS (id, name, city)
values (64, '������������ �������', 1);
insert into SUBWAYS (id, name, city)
values (65, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (66, '����������', 1);
insert into SUBWAYS (id, name, city)
values (67, '��������� ����', 1);
insert into SUBWAYS (id, name, city)
values (68, '���������', 1);
insert into SUBWAYS (id, name, city)
values (69, '����������', 1);
insert into SUBWAYS (id, name, city)
values (70, '�������', 1);
insert into SUBWAYS (id, name, city)
values (71, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (72, '��������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (73, '������������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (74, '�������', 1);
insert into SUBWAYS (id, name, city)
values (75, '�������', 1);
insert into SUBWAYS (id, name, city)
values (76, '������������', 1);
insert into SUBWAYS (id, name, city)
values (77, '������� ����', 1);
insert into SUBWAYS (id, name, city)
values (78, '�������', 1);
insert into SUBWAYS (id, name, city)
values (79, '����������', 1);
insert into SUBWAYS (id, name, city)
values (80, '����������', 1);
insert into SUBWAYS (id, name, city)
values (81, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (82, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (83, '������', 1);
insert into SUBWAYS (id, name, city)
values (84, '����������', 1);
insert into SUBWAYS (id, name, city)
values (85, '��������', 1);
insert into SUBWAYS (id, name, city)
values (86, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (87, '��������', 1);
insert into SUBWAYS (id, name, city)
values (88, '����������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (89, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (90, '����������', 1);
insert into SUBWAYS (id, name, city)
values (91, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (92, '��������������', 1);
insert into SUBWAYS (id, name, city)
values (93, '��������������', 1);
insert into SUBWAYS (id, name, city)
values (94, '����� ���������', 1);
insert into SUBWAYS (id, name, city)
values (95, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (96, '����������� ����', 1);
insert into SUBWAYS (id, name, city)
values (97, '�������', 1);
insert into SUBWAYS (id, name, city)
values (98, '��������', 1);
insert into SUBWAYS (id, name, city)
values (99, '������� ���', 1);
insert into SUBWAYS (id, name, city)
values (100, '����������', 1);
commit;
prompt 100 records committed...
insert into SUBWAYS (id, name, city)
values (102, '���� ������', 1);
insert into SUBWAYS (id, name, city)
values (101, '���� ��������', 1);
insert into SUBWAYS (id, name, city)
values (103, '������������', 1);
insert into SUBWAYS (id, name, city)
values (104, '������������', 1);
insert into SUBWAYS (id, name, city)
values (105, '������', 1);
insert into SUBWAYS (id, name, city)
values (106, '���������-�����������', 1);
insert into SUBWAYS (id, name, city)
values (107, '���������', 1);
insert into SUBWAYS (id, name, city)
values (108, '����������', 1);
insert into SUBWAYS (id, name, city)
values (109, '���������', 1);
insert into SUBWAYS (id, name, city)
values (110, '������� ������', 1);
insert into SUBWAYS (id, name, city)
values (111, '������� ���������', 1);
insert into SUBWAYS (id, name, city)
values (112, '������������', 1);
insert into SUBWAYS (id, name, city)
values (113, '�������', 1);
insert into SUBWAYS (id, name, city)
values (114, '��������', 1);
insert into SUBWAYS (id, name, city)
values (115, '�������������� �������', 1);
insert into SUBWAYS (id, name, city)
values (116, '������������', 1);
insert into SUBWAYS (id, name, city)
values (117, '�������� �����������', 1);
insert into SUBWAYS (id, name, city)
values (118, '�������� ����', 1);
insert into SUBWAYS (id, name, city)
values (119, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (120, '����������', 1);
insert into SUBWAYS (id, name, city)
values (121, '��������� �����', 1);
insert into SUBWAYS (id, name, city)
values (122, '������ ������', 1);
insert into SUBWAYS (id, name, city)
values (123, '�������', 1);
insert into SUBWAYS (id, name, city)
values (124, '�������', 1);
insert into SUBWAYS (id, name, city)
values (125, '��������� ��������', 1);
insert into SUBWAYS (id, name, city)
values (126, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (127, '��������', 1);
insert into SUBWAYS (id, name, city)
values (128, '���������������', 1);
insert into SUBWAYS (id, name, city)
values (129, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (130, '������������', 1);
insert into SUBWAYS (id, name, city)
values (131, '���������� �������', 1);
insert into SUBWAYS (id, name, city)
values (132, '���������� (��������-���������� �����)', 1);
insert into SUBWAYS (id, name, city)
values (133, '���������� (��������� �����)', 1);
insert into SUBWAYS (id, name, city)
values (134, '�����', 1);
insert into SUBWAYS (id, name, city)
values (135, '����������', 1);
insert into SUBWAYS (id, name, city)
values (136, '�������', 1);
insert into SUBWAYS (id, name, city)
values (137, '����������', 1);
insert into SUBWAYS (id, name, city)
values (138, '���������� �������', 1);
insert into SUBWAYS (id, name, city)
values (139, '��������', 1);
insert into SUBWAYS (id, name, city)
values (140, '������������', 1);
insert into SUBWAYS (id, name, city)
values (141, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (142, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (143, '���������', 1);
insert into SUBWAYS (id, name, city)
values (144, '��������', 1);
insert into SUBWAYS (id, name, city)
values (145, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (146, '������������', 1);
insert into SUBWAYS (id, name, city)
values (147, '������ ����', 1);
insert into SUBWAYS (id, name, city)
values (148, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (149, '�������������', 1);
insert into SUBWAYS (id, name, city)
values (150, '���������', 1);
insert into SUBWAYS (id, name, city)
values (151, '�������', 1);
insert into SUBWAYS (id, name, city)
values (152, '��������', 1);
insert into SUBWAYS (id, name, city)
values (153, '������������', 1);
insert into SUBWAYS (id, name, city)
values (154, '���������', 1);
insert into SUBWAYS (id, name, city)
values (159, '����� 1905 ����', 1);
insert into SUBWAYS (id, name, city)
values (155, '����� ��������� ������', 1);
insert into SUBWAYS (id, name, city)
values (156, '����� ���������', 1);
insert into SUBWAYS (id, name, city)
values (157, '����� ������������', 1);
insert into SUBWAYS (id, name, city)
values (158, '����� ����������������', 1);
insert into SUBWAYS (id, name, city)
values (160, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (161, '��������� ����', 1);
insert into SUBWAYS (id, name, city)
values (162, '����', 1);
insert into SUBWAYS (id, name, city)
values (163, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (164, '��������', 1);
insert into SUBWAYS (id, name, city)
values (165, '������� �������', 1);
insert into SUBWAYS (id, name, city)
values (166, '������������', 1);
insert into SUBWAYS (id, name, city)
values (167, '������������', 1);
insert into SUBWAYS (id, name, city)
values (168, '���������', 1);
insert into SUBWAYS (id, name, city)
values (169, '������ �����', 1);
insert into SUBWAYS (id, name, city)
values (170, '����������', 1);
insert into SUBWAYS (id, name, city)
values (171, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (172, '�����������', 1);
insert into SUBWAYS (id, name, city)
values (173, '����� �����������', 1);
insert into SUBWAYS (id, name, city)
values (174, '����������', 1);
insert into SUBWAYS (id, name, city)
values (175, '���������', 1);
insert into SUBWAYS (id, name, city)
values (176, '����������������', 1);
insert into SUBWAYS (id, name, city)
values (177, '���-��������', 1);
insert into SUBWAYS (id, name, city)
values (178, '�����', 1);
insert into SUBWAYS (id, name, city)
values (179, '�������', 1);
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
