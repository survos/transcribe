<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200109183441 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE marker_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE clip_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE final_cut_pro_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE timeline_format_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE project_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE broll_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE word_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE media_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE timeline_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE timeline_asset_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE marker (id INT NOT NULL, media_id INT NOT NULL, title VARCHAR(255) NOT NULL, note TEXT DEFAULT NULL, color VARCHAR(32) DEFAULT NULL, idx INT DEFAULT NULL, first_word_index INT NOT NULL, last_word_index INT NOT NULL, irrelevant BOOLEAN DEFAULT NULL, hidden BOOLEAN DEFAULT NULL, start_time INT DEFAULT NULL, end_time INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82CF20FEEA9FDD75 ON marker (media_id)');
        $this->addSql('CREATE TABLE clip (id INT NOT NULL, asset_id INT NOT NULL, format_id INT NOT NULL, timeline_id INT NOT NULL, type VARCHAR(12) NOT NULL, name VARCHAR(64) NOT NULL, duration NUMERIC(6, 1) DEFAULT NULL, start NUMERIC(6, 1) DEFAULT NULL, track_offset NUMERIC(6, 1) DEFAULT NULL, lane VARCHAR(6) DEFAULT NULL, track_offset_string VARCHAR(32) DEFAULT NULL, duration_string VARCHAR(32) DEFAULT NULL, audio_start_string VARCHAR(32) DEFAULT NULL, audio_duration_string VARCHAR(32) DEFAULT NULL, start_string VARCHAR(32) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD2014675DA1941 ON clip (asset_id)');
        $this->addSql('CREATE INDEX IDX_AD201467D629F605 ON clip (format_id)');
        $this->addSql('CREATE INDEX IDX_AD201467EDBEDD37 ON clip (timeline_id)');
        $this->addSql('CREATE TABLE final_cut_pro (id INT NOT NULL, version VARCHAR(12) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE timeline_format (id INT NOT NULL, timeline_id INT NOT NULL, code VARCHAR(16) NOT NULL, name VARCHAR(32) NOT NULL, height INT DEFAULT NULL, width INT NOT NULL, frame_duration_string VARCHAR(16) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4125F8F7EDBEDD37 ON timeline_format (timeline_id)');
        $this->addSql('CREATE TABLE project (id INT NOT NULL, last_marker_id INT DEFAULT NULL, code VARCHAR(32) NOT NULL, base_path VARCHAR(255) NOT NULL, honoree_name VARCHAR(255) DEFAULT NULL, honoree_title VARCHAR(255) DEFAULT NULL, music VARCHAR(255) DEFAULT NULL, signs TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2FB3D0EE508BBFED ON project (last_marker_id)');
        $this->addSql('CREATE TABLE broll (id INT NOT NULL, marker_id INT NOT NULL, media_id INT DEFAULT NULL, clip_id INT DEFAULT NULL, code VARCHAR(128) NOT NULL, start_word VARCHAR(64) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2A213EE2474460EB ON broll (marker_id)');
        $this->addSql('CREATE INDEX IDX_2A213EE2EA9FDD75 ON broll (media_id)');
        $this->addSql('CREATE INDEX IDX_2A213EE23E19EFA5 ON broll (clip_id)');
        $this->addSql('CREATE TABLE word (id INT NOT NULL, media_id INT NOT NULL, marker_id INT DEFAULT NULL, word VARCHAR(64) NOT NULL, start_time DOUBLE PRECISION NOT NULL, end_time DOUBLE PRECISION NOT NULL, end_punctuation VARCHAR(2) DEFAULT NULL, idx INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C3F17511EA9FDD75 ON word (media_id)');
        $this->addSql('CREATE INDEX IDX_C3F17511474460EB ON word (marker_id)');
        $this->addSql('CREATE UNIQUE INDEX medix_idx_unique ON word (media_id, idx)');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE media (id INT NOT NULL, project_id INT NOT NULL, filename VARCHAR(80) NOT NULL, path VARCHAR(255) NOT NULL, flac_exists BOOLEAN NOT NULL, transcript_json TEXT DEFAULT NULL, transcript_requested BOOLEAN NOT NULL, word_count INT DEFAULT NULL, file_size BIGINT DEFAULT NULL, duration INT DEFAULT NULL, speaker VARCHAR(32) DEFAULT NULL, display VARCHAR(80) DEFAULT NULL, code VARCHAR(64) DEFAULT NULL, streams_json TEXT DEFAULT NULL, stream_count INT DEFAULT NULL, type VARCHAR(32) DEFAULT NULL, video_stream TEXT DEFAULT NULL, audio_streams TEXT DEFAULT NULL, height INT DEFAULT NULL, width INT DEFAULT NULL, frame_rate VARCHAR(16) DEFAULT NULL, frame_duration VARCHAR(16) DEFAULT NULL, lower_thirds TEXT DEFAULT NULL, marking VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_6A2CA10C166D1F9C ON media (project_id)');
        $this->addSql('CREATE INDEX code ON media (code)');
        $this->addSql('CREATE UNIQUE INDEX project_code ON media (project_id, code)');
        $this->addSql('COMMENT ON COLUMN media.audio_streams IS \'(DC2Type:array)\'');
        $this->addSql('CREATE TABLE timeline (id INT NOT NULL, project_id INT NOT NULL, code VARCHAR(32) NOT NULL, gap_time INT DEFAULT NULL, max_duration INT DEFAULT NULL, total_duration NUMERIC(10, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_46FEC666166D1F9C ON timeline (project_id)');
        $this->addSql('CREATE TABLE timeline_marker (timeline_id INT NOT NULL, marker_id INT NOT NULL, PRIMARY KEY(timeline_id, marker_id))');
        $this->addSql('CREATE INDEX IDX_1D50AAD6EDBEDD37 ON timeline_marker (timeline_id)');
        $this->addSql('CREATE INDEX IDX_1D50AAD6474460EB ON timeline_marker (marker_id)');
        $this->addSql('CREATE TABLE timeline_asset (id INT NOT NULL, format_id INT NOT NULL, timeline_id INT NOT NULL, media_id INT DEFAULT NULL, code VARCHAR(16) NOT NULL, name VARCHAR(64) NOT NULL, src VARCHAR(128) NOT NULL, audio_sources INT DEFAULT NULL, has_video BOOLEAN DEFAULT NULL, duration NUMERIC(8, 1) DEFAULT NULL, has_audio BOOLEAN DEFAULT NULL, audio_channels INT DEFAULT NULL, start NUMERIC(6, 2) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_22C1D71AD629F605 ON timeline_asset (format_id)');
        $this->addSql('CREATE INDEX IDX_22C1D71AEDBEDD37 ON timeline_asset (timeline_id)');
        $this->addSql('CREATE INDEX IDX_22C1D71AEA9FDD75 ON timeline_asset (media_id)');
        $this->addSql('ALTER TABLE marker ADD CONSTRAINT FK_82CF20FEEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clip ADD CONSTRAINT FK_AD2014675DA1941 FOREIGN KEY (asset_id) REFERENCES timeline_asset (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clip ADD CONSTRAINT FK_AD201467D629F605 FOREIGN KEY (format_id) REFERENCES timeline_format (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE clip ADD CONSTRAINT FK_AD201467EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_format ADD CONSTRAINT FK_4125F8F7EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE508BBFED FOREIGN KEY (last_marker_id) REFERENCES marker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE broll ADD CONSTRAINT FK_2A213EE2474460EB FOREIGN KEY (marker_id) REFERENCES marker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE broll ADD CONSTRAINT FK_2A213EE2EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE broll ADD CONSTRAINT FK_2A213EE23E19EFA5 FOREIGN KEY (clip_id) REFERENCES clip (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE word ADD CONSTRAINT FK_C3F17511EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE word ADD CONSTRAINT FK_C3F17511474460EB FOREIGN KEY (marker_id) REFERENCES marker (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline ADD CONSTRAINT FK_46FEC666166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_marker ADD CONSTRAINT FK_1D50AAD6EDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_marker ADD CONSTRAINT FK_1D50AAD6474460EB FOREIGN KEY (marker_id) REFERENCES marker (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_asset ADD CONSTRAINT FK_22C1D71AD629F605 FOREIGN KEY (format_id) REFERENCES timeline_format (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_asset ADD CONSTRAINT FK_22C1D71AEDBEDD37 FOREIGN KEY (timeline_id) REFERENCES timeline (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE timeline_asset ADD CONSTRAINT FK_22C1D71AEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE project DROP CONSTRAINT FK_2FB3D0EE508BBFED');
        $this->addSql('ALTER TABLE broll DROP CONSTRAINT FK_2A213EE2474460EB');
        $this->addSql('ALTER TABLE word DROP CONSTRAINT FK_C3F17511474460EB');
        $this->addSql('ALTER TABLE timeline_marker DROP CONSTRAINT FK_1D50AAD6474460EB');
        $this->addSql('ALTER TABLE broll DROP CONSTRAINT FK_2A213EE23E19EFA5');
        $this->addSql('ALTER TABLE clip DROP CONSTRAINT FK_AD201467D629F605');
        $this->addSql('ALTER TABLE timeline_asset DROP CONSTRAINT FK_22C1D71AD629F605');
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C166D1F9C');
        $this->addSql('ALTER TABLE timeline DROP CONSTRAINT FK_46FEC666166D1F9C');
        $this->addSql('ALTER TABLE marker DROP CONSTRAINT FK_82CF20FEEA9FDD75');
        $this->addSql('ALTER TABLE broll DROP CONSTRAINT FK_2A213EE2EA9FDD75');
        $this->addSql('ALTER TABLE word DROP CONSTRAINT FK_C3F17511EA9FDD75');
        $this->addSql('ALTER TABLE timeline_asset DROP CONSTRAINT FK_22C1D71AEA9FDD75');
        $this->addSql('ALTER TABLE clip DROP CONSTRAINT FK_AD201467EDBEDD37');
        $this->addSql('ALTER TABLE timeline_format DROP CONSTRAINT FK_4125F8F7EDBEDD37');
        $this->addSql('ALTER TABLE timeline_marker DROP CONSTRAINT FK_1D50AAD6EDBEDD37');
        $this->addSql('ALTER TABLE timeline_asset DROP CONSTRAINT FK_22C1D71AEDBEDD37');
        $this->addSql('ALTER TABLE clip DROP CONSTRAINT FK_AD2014675DA1941');
        $this->addSql('DROP SEQUENCE marker_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE clip_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE final_cut_pro_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE timeline_format_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE project_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE broll_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE word_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE media_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE timeline_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE timeline_asset_id_seq CASCADE');
        $this->addSql('DROP TABLE marker');
        $this->addSql('DROP TABLE clip');
        $this->addSql('DROP TABLE final_cut_pro');
        $this->addSql('DROP TABLE timeline_format');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE broll');
        $this->addSql('DROP TABLE word');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE timeline');
        $this->addSql('DROP TABLE timeline_marker');
        $this->addSql('DROP TABLE timeline_asset');
    }
}
