<?php
/**
 * LecturerDetailModel - Handle lecturer profile details
 */

class LecturerDetailModel
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /**
     * Get lecturer full profile with all details
     */
    public function getLecturerProfile($id)
    {
        // Get basic info
        $query = "SELECT * FROM team_members WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$profile) {
            return null;
        }

        // Get projects
        $profile['projects'] = $this->getProjects($id);
        
        // Get education
        $profile['education'] = $this->getEducation($id);
        
        // Get skills
        $profile['skills'] = $this->getSkills($id);
        
        // Get interests
        $profile['interests'] = $this->getInterests($id);
        
        // Get publications
        $profile['publications'] = $this->getPublications($id);

        return $profile;
    }

    /**
     * Get lecturer projects
     */
    public function getProjects($team_member_id, $limit = null)
    {
        $query = "SELECT * FROM lecturer_projects 
                 WHERE team_member_id = :id 
                 ORDER BY start_date DESC";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $team_member_id);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lecturer education history
     */
    public function getEducation($team_member_id)
    {
        $query = "SELECT * FROM lecturer_education 
                 WHERE team_member_id = :id 
                 ORDER BY end_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $team_member_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lecturer skills grouped by category
     */
    public function getSkills($team_member_id)
    {
        $query = "SELECT * FROM lecturer_skills 
                 WHERE team_member_id = :id 
                 ORDER BY category, skill_name";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $team_member_id);
        $stmt->execute();
        $skills = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group by category
        $grouped = [];
        foreach ($skills as $skill) {
            $category = $skill['category'];
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $skill;
        }

        return $grouped;
    }

    /**
     * Get lecturer interests
     */
    public function getInterests($team_member_id)
    {
        $query = "SELECT * FROM lecturer_interests 
                 WHERE team_member_id = :id 
                 ORDER BY created_at";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $team_member_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get lecturer publications
     */
    public function getPublications($team_member_id, $limit = null)
    {
        $query = "SELECT * FROM lecturer_publications 
                 WHERE team_member_id = :id 
                 ORDER BY year DESC, title";
        
        if ($limit) {
            $query .= " LIMIT :limit";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $team_member_id);
        
        if ($limit) {
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add project
     */
    public function addProject($data)
    {
        $query = "INSERT INTO lecturer_projects 
                 (team_member_id, title, category, description, start_date, end_date, status) 
                 VALUES (:member_id, :title, :category, :description, :start_date, :end_date, :status)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * Add education
     */
    public function addEducation($data)
    {
        $query = "INSERT INTO lecturer_education 
                 (team_member_id, institution, degree, field_of_study, start_date, end_date, description) 
                 VALUES (:member_id, :institution, :degree, :field, :start_date, :end_date, :description)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * Add skill
     */
    public function addSkill($data)
    {
        $query = "INSERT INTO lecturer_skills 
                 (team_member_id, category, skill_name, skill_icon, proficiency) 
                 VALUES (:member_id, :category, :skill_name, :skill_icon, :proficiency)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * Add interest
     */
    public function addInterest($team_member_id, $interest_text)
    {
        $query = "INSERT INTO lecturer_interests (team_member_id, interest_text) 
                 VALUES (:member_id, :text)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':member_id', $team_member_id);
        $stmt->bindParam(':text', $interest_text);
        return $stmt->execute();
    }

    /**
     * Add publication
     */
    public function addPublication($data)
    {
        $query = "INSERT INTO lecturer_publications 
                 (team_member_id, title, year, url, type) 
                 VALUES (:member_id, :title, :year, :url, :type)";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($data);
    }

    /**
     * Delete project
     */
    public function deleteProject($id)
    {
        $query = "DELETE FROM lecturer_projects WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Delete education
     */
    public function deleteEducation($id)
    {
        $query = "DELETE FROM lecturer_education WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Delete skill
     */
    public function deleteSkill($id)
    {
        $query = "DELETE FROM lecturer_skills WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Delete interest
     */
    public function deleteInterest($id)
    {
        $query = "DELETE FROM lecturer_interests WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Delete publication
     */
    public function deletePublication($id)
    {
        $query = "DELETE FROM lecturer_publications WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Update team member basic info
     */
    public function updateBasicInfo($id, $data)
    {
        // Only update the fields that are provided in the form (Basic Info tab)
        // Name and position are managed in Team Members page
        $query = "UPDATE team_members SET 
                 bio = :bio,
                 address = :address,
                 phone = :phone,
                 email = :email,
                 linkedin_url = :linkedin,
                 google_scholar_url = :scholar,
                 researchgate_url = :researchgate,
                 orcid_url = :orcid
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':bio', $data['bio']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':phone', $data['phone']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':linkedin', $data['linkedin_url']);
        $stmt->bindParam(':scholar', $data['google_scholar_url']);
        $stmt->bindParam(':researchgate', $data['researchgate_url']);
        $stmt->bindParam(':orcid', $data['orcid_url']);
        
        return $stmt->execute();
    }

    /**
     * Update project
     */
    public function updateProject($project_id, $member_id, $data)
    {
        $query = "UPDATE lecturer_projects SET 
                 title = :title,
                 category = :category,
                 description = :description,
                 start_date = :start_date,
                 end_date = :end_date
                 WHERE id = :project_id AND team_member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':project_id', $project_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        
        return $stmt->execute();
    }

    /**
     * Update education
     */
    public function updateEducation($education_id, $member_id, $data)
    {
        $query = "UPDATE lecturer_education SET 
                 institution = :institution,
                 degree = :degree,
                 field_of_study = :field_of_study,
                 description = :description,
                 start_date = :start_date,
                 end_date = :end_date
                 WHERE id = :education_id AND team_member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':education_id', $education_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':institution', $data['institution']);
        $stmt->bindParam(':degree', $data['degree']);
        $stmt->bindParam(':field_of_study', $data['field_of_study']);
        $stmt->bindParam(':description', $data['description']);
        $stmt->bindParam(':start_date', $data['start_date']);
        $stmt->bindParam(':end_date', $data['end_date']);
        
        return $stmt->execute();
    }

    /**
     * Update skill
     */
    public function updateSkill($skill_id, $member_id, $data)
    {
        $query = "UPDATE lecturer_skills SET 
                 skill_name = :skill_name,
                 category = :category,
                 skill_icon = :skill_icon
                 WHERE id = :skill_id AND team_member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':skill_id', $skill_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':skill_name', $data['skill_name']);
        $stmt->bindParam(':category', $data['category']);
        $stmt->bindParam(':skill_icon', $data['skill_icon']);
        
        return $stmt->execute();
    }

    /**
     * Update interest
     */
    public function updateInterest($interest_id, $member_id, $data)
    {
        $query = "UPDATE lecturer_interests SET 
                 interest_text = :interest_text
                 WHERE id = :interest_id AND team_member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':interest_id', $interest_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':interest_text', $data['interest_text']);
        
        return $stmt->execute();
    }

    /**
     * Update publication
     */
    public function updatePublication($publication_id, $member_id, $data)
    {
        $query = "UPDATE lecturer_publications SET 
                 title = :title,
                 year = :year,
                 url = :url
                 WHERE id = :publication_id AND team_member_id = :member_id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':publication_id', $publication_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->bindParam(':title', $data['title']);
        $stmt->bindParam(':year', $data['year']);
        $stmt->bindParam(':url', $data['url']);
        
        return $stmt->execute();
    }
}
