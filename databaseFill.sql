
-- PARA EDITAR MUCHOS CAMPOS A LA VEZ, SI ESTAN ALINEADOS PUEDES HACER COMMAND+OPTION DANDOLE A LA FLECHA PARA ABAJO PARA CREAR NUEVOS CURSORES Y EDITAR TODAS LAS LINEAS A LA VEZ
-- PARA EDITAR VARIOS QUE NO ESTÉN ALINEADOS DANDO CLICK CON OPTION PULSADO CREARÁ EN LA POSICIÓN SELECCIONADA UN CURSOR NUEVO

-- MUCHO CUIDADO QUE ES MUY FACIL EDITAR COSAS QUE NO SE QUIEREN EDITAR


-- PERIODOS Y TABLA INTERMEDIA DE PERIODOS Y ASIGNATURAS (ASEGURARSE DE QUE EL USUARIO TIENE LAS ASIGNATURAS)

INSERT INTO `periods` (`name`, `date_start`, `date_end`, `created_at`, `updated_at`, `student_id`)
VALUES
	('Primer trimestre', 1631704831, 1640258431, NULL, NULL, 1),
	('Segundo trimestre', 1641813631, 1648466431, NULL, NULL, 1),
	('Tercer trimestre', 1648552831, 1655292031, NULL, NULL, 1);

INSERT INTO `contains` (`period_id`, `subject_id`, `created_at`, `updated_at`)
VALUES
	(1, 1, NULL, NULL),
	(1, 2, NULL, NULL),
	(2, 1, NULL, NULL),
	(2, 2, NULL, NULL),
	(2, 3, NULL, NULL),
	(2, 4, NULL, NULL),
	(2, 5, NULL, NULL),
	(2, 6, NULL, NULL),
	(2, 7, NULL, NULL),
	(3, 1, NULL, NULL),
	(3, 2, NULL, NULL);



-- BLOQUES

INSERT INTO `blocks` (`time_start`, `time_end`, `day`, `created_at`, `updated_at`, `student_id`, `subject_id`, `period_id`)
VALUES
	('1646730000', '1646736000', 1, NULL, NULL, 1, 1, 2),
	('1646737200', '1646743200', 1, NULL, NULL, 1, 3, 2),
	('1646744400', '1646750400', 1, NULL, NULL, 1, 4, 2),

	('1646730000', '1646736000', 2, NULL, NULL, 1, 4, 2),
	('1646737200', '1646743200', 2, NULL, NULL, 1, 4, 2),
	('1646744400', '1646750400', 1, NULL, NULL, 1, 2, 2),

	('1646730000', '1646736000', 3, NULL, NULL, 1, 5, 2),
	('1646737200', '1646743200', 3, NULL, NULL, 1, 6, 2),
	('1646744400', '1646750400', 3, NULL, NULL, 1, 6, 2),

	('1646730000', '1646736000', 4, NULL, NULL, 1, 7, 2),
	('1646737200', '1646743200', 4, NULL, NULL, 1, 1, 2),
	('1646744400', '1646750400', 4, NULL, NULL, 1, 2, 2),

	('1646730000', '1646736000', 5, NULL, NULL, 1, 3, 2),
	('1646737200', '1646743200', 5, NULL, NULL, 1, 4, 2),
	('1646744400', '1646750400', 5, NULL, NULL, 1, 7, 2);




-- SESIONES (TODOS LOS DATOS VAN EN SEGUNDOS, EL TIEMPO TOTAL NO TIENE POR QUE SER LA MULTIPLICACIÓN DE LOS OTROS (PUEDE ESTUDIARSE TIEMPO EXTRA O NO FINALIZARSE))

INSERT INTO `sessions` (`quantity`, `duration`, `total_time`, `created_at`, `updated_at`, `task_id`, `student_id`)
VALUES
	(2, 900, 1920, NULL, NULL, 1, 1),
	(4, 900, 3600, NULL, NULL, 4, 1),
	(5, 600, 3060, NULL, NULL, 2, 1),
	(2, 1600, 3000, NULL, NULL, 3, 1);


-- EVENTOS

INSERT INTO `events` (`name`, `type`, `all_day`, `notes`, `timestamp_start`, `timestamp_end`, `created_at`, `updated_at`, `subject_id`, `student_id`)
VALUES
	('Dia del carmen', 'vacation', 1, 'Notas del dia del carmen', 1657929600, 1658015999, NULL, NULL, NULL, 1),
	('Dia del padre', 'vacation', 1, 'Notas del dia del padre', 1655596800, 1655683199, NULL, NULL, NULL, 1),
	('Dia de la madre', 'vacation', 1, 'Notas del dia de la madre', 1651363200, 1651449599, NULL, NULL, NULL, 1),
	('Dia de la mujer', 'vacation', 1, 'Notas del dia de la mujer', 1646697600, 1646783999, NULL, NULL, NULL, 1),
	('Exámen empresa', 'exam', 0, 'Notas de examen de empresa', 1647334800, 1647344400, NULL, NULL, 1, 1),
	('Exámen matematicas', 'exam', 0, 'Notas de examen de matematicas', 1648900800, 1648906800, NULL, NULL, 2, 1),
	('Cita medica', 'personal', 0, 'Notas de cita medica', 1652436600, 1652439600, NULL, NULL, NULL, 1);
