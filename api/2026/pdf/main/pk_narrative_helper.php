<?php

function pk_narrative_clean_label($value)
{
    $value = strip_tags((string) $value);
    $value = preg_replace('/\s+/', ' ', $value);

    return trim($value);
}

function pk_narrative_format_target($target, $unitLabel)
{
    $targetLabel = trim((string) $target);
    $unitLabel = pk_narrative_clean_label($unitLabel);

    if ($targetLabel === '' || $targetLabel === '0' || $targetLabel === '0,00' || $targetLabel === '0.00') {
        return 'sesuai penetapan kinerja';
    }

    if ($unitLabel === '') {
        return $targetLabel;
    }

    return $targetLabel . ' ' . $unitLabel;
}

function build_pk_target_narrative($programLabel, $sasaranLabel, $indicatorLabel, $target, $unitLabel, $year)
{
    $programLabel = pk_narrative_clean_label($programLabel);
    $sasaranLabel = pk_narrative_clean_label($sasaranLabel);
    $indicatorLabel = pk_narrative_clean_label($indicatorLabel);
    $targetLabel = pk_narrative_format_target($target, $unitLabel);
    $yearLabel = trim((string) $year);

    $segments = array();

    if ($programLabel !== '') {
        $segments[] = 'Program ' . $programLabel;
    } else {
        $segments[] = 'Program ini';
    }

    if ($yearLabel !== '') {
        $segments[] = 'Tahun ' . $yearLabel;
    }

    $opening = implode(' ', $segments);

    if ($sasaranLabel !== '') {
        $opening .= ' pada sasaran kegiatan ' . $sasaranLabel;
    }

    if ($indicatorLabel !== '') {
        $opening .= ' menargetkan capaian indikator ' . $indicatorLabel;
    } else {
        $opening .= ' menargetkan capaian kinerja';
    }

    $opening .= ' sebesar ' . $targetLabel . '.';

    return $opening . ' Target ini menjadi dasar pelaksanaan kegiatan tahun berjalan untuk mendukung pencapaian sasaran program, peningkatan layanan, dan hasil pembangunan sesuai dokumen perencanaan.';
}
