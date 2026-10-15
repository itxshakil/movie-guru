/**
 * Builds a single-event iCalendar (.ics) file, e.g. for a release date or a movie night.
 */

function escapeText(value) {
    return String(value)
        .replace(/\\/g, '\\\\')
        .replace(/\n/g, '\\n')
        .replace(/([,;])/g, '\\$1');
}

/**
 * Folds content lines longer than 75 octets, as RFC 5545 requires.
 */
function foldLine(line) {
    const encoder = new TextEncoder();
    const parts = [];
    let current = '';

    for (const character of line) {
        if (encoder.encode(current + character).length > (parts.length === 0 ? 75 : 74)) {
            parts.push(current);
            current = character;
        } else {
            current += character;
        }
    }
    parts.push(current);

    return parts.join('\r\n ');
}

const pad = (value) => String(value).padStart(2, '0');

const formatUtcDateTime = (date) => `${date.getUTCFullYear()}${pad(date.getUTCMonth() + 1)}${pad(date.getUTCDate())}`
    + `T${pad(date.getUTCHours())}${pad(date.getUTCMinutes())}${pad(date.getUTCSeconds())}Z`;

const formatDate = (date) => `${date.getFullYear()}${pad(date.getMonth() + 1)}${pad(date.getDate())}`;

function slugify(value) {
    return String(value).toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') || 'event';
}

/**
 * @param {{title: string, description?: string, url?: string, start: Date, allDay?: boolean, durationMinutes?: number}} event
 * @returns {File}
 */
export function createCalendarFile({title, description = '', url = '', start, allDay = false, durationMinutes = 150}) {
    const timing = allDay
        ? [`DTSTART;VALUE=DATE:${formatDate(start)}`, `DTEND;VALUE=DATE:${formatDate(new Date(start.getFullYear(), start.getMonth(), start.getDate() + 1))}`]
        : [`DTSTART:${formatUtcDateTime(start)}`, `DTEND:${formatUtcDateTime(new Date(start.getTime() + durationMinutes * 60_000))}`];

    const lines = [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'PRODID:-//Movie Guru//EN',
        'CALSCALE:GREGORIAN',
        'METHOD:PUBLISH',
        'BEGIN:VEVENT',
        `UID:${crypto.randomUUID()}@movieguru`,
        `DTSTAMP:${formatUtcDateTime(new Date())}`,
        ...timing,
        `SUMMARY:${escapeText(title)}`,
        ...(description ? [`DESCRIPTION:${escapeText(description)}`] : []),
        ...(url ? [`URL:${url}`] : []),
        'END:VEVENT',
        'END:VCALENDAR',
    ];

    return new File([lines.map(foldLine).join('\r\n') + '\r\n'], `${slugify(title)}.ics`, {type: 'text/calendar'});
}
